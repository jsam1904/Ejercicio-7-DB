<?php

namespace App\Queries;

use App\Models\Contract;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Consultas Eloquent de demostración para el dominio de Fútbol Americano NFL.
 *
 * Ejecutar desde tinker:
 *   php artisan tinker
 *   use App\Queries\FootballQueries;
 *   FootballQueries::equiposNFCConRoster();
 */
class FootballQueries
{
    // =========================================================================
    // CONSULTA 1 — Eager Loading (evita N+1)
    // =========================================================================

    /**
     * Devuelve todos los equipos de la conferencia NFC con su división, conferencia
     * y el roster activo de jugadores.
     *
     * ¿Por qué Eager Loading aquí?
     * Sin with(), iterar sobre los 16 equipos NFC y acceder a $team->division,
     * $team->division->conference y $team->players dispararía 16 × 3 = 48 consultas
     * adicionales (problema N+1). Con with(), Eloquent carga todo en 4 consultas totales
     * independientemente del número de equipos devueltos.
     */
    public static function equiposNFCConRoster(): Collection
    {
        return Team::with([
            'division.conference',
            'players' => fn($q) => $q->where('is_active', true)->orderBy('last_name'),
            'coaches' => fn($q) => $q->where('is_active', true),
        ])
            ->whereHas('division.conference', fn($q) => $q->where('name', 'NFC'))
            ->orderBy('name')
            ->get();
    }

    // =========================================================================
    // CONSULTA 2 — Top 10 mariscales de campo por yardas de pase en una temporada
    // =========================================================================

    /**
     * Devuelve el ranking de los 10 mejores QBs por yardas totales de pase en una temporada.
     * Usa relación playerGameStats + filtro por posición + agregación + ordenamiento.
     */
    public static function topQBsPorYardasDePase(int $year = 2023, int $limite = 10): Collection
    {
        return Player::with('team')
            ->where('position', 'QB')
            ->withSum(
                ['playerGameStats as total_passing_yards' => function ($query) use ($year) {
                    $query->whereHas('game.season', fn($q) => $q->where('year', $year));
                }],
                'passing_yards'
            )
            ->withSum(
                ['playerGameStats as total_touchdowns' => function ($query) use ($year) {
                    $query->whereHas('game.season', fn($q) => $q->where('year', $year));
                }],
                'passing_touchdowns'
            )
            ->orderByDesc('total_passing_yards')
            ->limit($limite)
            ->get();
    }

    // =========================================================================
    // CONSULTA 3 — Partidos en los que el margen fue mayor a 21 puntos ("palizas")
    // =========================================================================

    /**
     * Retorna los últimos 20 partidos con diferencia de puntuación mayor a 21.
     * Demuestra whereRaw() + relaciones homeTeam, awayTeam, season, stadium.
     */
    public static function partidosPorGoleada(int $margen = 21): Collection
    {
        return Game::with(['homeTeam', 'awayTeam', 'season', 'stadium'])
            ->whereNotNull('home_score')
            ->whereRaw('ABS(home_score - away_score) > ?', [$margen])
            ->orderByDesc('game_date')
            ->limit(20)
            ->get();
    }

    // =========================================================================
    // CONSULTA 4 — Jugadores activos con lesiones sin fecha de regreso (IR)
    // =========================================================================

    /**
     * Lista jugadores activos que actualmente están en la lista de lesionados (return_date nulo).
     * Usa whereHas + carga condicional de relaciones + ordenamiento por apellido.
     */
    public static function jugadoresEnListaDeLesionados(): Collection
    {
        return Player::with([
            'team',
            'injuries' => fn($q) => $q->whereNull('return_date')->latest('injury_date'),
        ])
            ->where('is_active', true)
            ->whereHas('injuries', fn($q) => $q->whereNull('return_date'))
            ->orderBy('last_name')
            ->get();
    }

    // =========================================================================
    // CONSULTA 5 — Contratos activos de alto valor que vencen este año
    // =========================================================================

    /**
     * Devuelve contratos activos que vencen en el año indicado con salario > $5M,
     * ordenados de mayor a menor salario. Relaciona player y team.
     */
    public static function contratosProximosAVencer(int $year = 2025): Collection
    {
        return Contract::with(['player', 'team'])
            ->where('is_active', true)
            ->where('end_year', $year)
            ->where('annual_salary', '>', 5_000_000)
            ->orderByDesc('annual_salary')
            ->get();
    }

    // =========================================================================
    // CONSULTA 6 — Tabla de posiciones: victorias por equipo en una temporada
    // =========================================================================

    /**
     * Calcula victorias de local y visitante por equipo en una temporada dada.
     * Combina withCount() con closures filtradas por season, luego mapea y ordena
     * la colección en PHP para obtener el ranking final.
     */
    public static function tablaPosiciones(int $year = 2023): BaseCollection
    {
        return Team::with('division')
            ->withCount([
                'homeGames as home_wins' => fn($q) => $q
                    ->whereHas('season', fn($s) => $s->where('year', $year))
                    ->whereColumn('home_score', '>', 'away_score'),
                'awayGames as away_wins' => fn($q) => $q
                    ->whereHas('season', fn($s) => $s->where('year', $year))
                    ->whereColumn('away_score', '>', 'home_score'),
                'homeGames as home_losses' => fn($q) => $q
                    ->whereHas('season', fn($s) => $s->where('year', $year))
                    ->whereColumn('home_score', '<', 'away_score'),
                'awayGames as away_losses' => fn($q) => $q
                    ->whereHas('season', fn($s) => $s->where('year', $year))
                    ->whereColumn('away_score', '<', 'home_score'),
            ])
            ->get()
            ->map(fn($team) => [
                'equipo'    => "{$team->city} {$team->name}",
                'division'  => $team->division->name ?? '—',
                'victorias' => $team->home_wins + $team->away_wins,
                'derrotas'  => $team->home_losses + $team->away_losses,
            ])
            ->sortByDesc('victorias')
            ->values();
    }
}
