# Ejercicio 7 — Base de Datos NFL (Fútbol Americano)

Proyecto Laravel con base de datos relacional sobre la NFL usando Eloquent ORM.
Incluye 12 migraciones, 12 modelos, 6 relaciones y 6 consultas Eloquent con más de 23 000 registros.

---

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo

---

## Levantar los contenedores

```bash
docker compose up -d
```

| Contenedor | Imagen | Puerto |
| --- | --- | --- |
| `laravel_app` | PHP 8.3 + Composer | interno 9000 |
| `laravel_nginx` | Nginx | <http://localhost:8000> |
| `laravel_db` | MySQL 8.0 | localhost:3306 |

---

## Primera vez — instalación completa

Ejecutar los tres comandos en orden:

```bash
# 1. Instalar dependencias PHP
docker exec laravel_app composer install --ignore-platform-reqs --no-interaction

# 2. Generar la clave de la aplicación
docker exec laravel_app php artisan key:generate

# 3. Crear las 12 tablas y poblar la base de datos (23 252 registros)
docker exec laravel_app php artisan migrate:fresh --seed
```

---

## Comandos útiles

```bash
# Borrar todo y volver a crear + sembrar
docker exec laravel_app php artisan migrate:fresh --seed

# Solo correr migraciones pendientes
docker exec laravel_app php artisan migrate

# Solo correr el seeder
docker exec laravel_app php artisan db:seed

# Ver estado de cada migración
docker exec laravel_app php artisan migrate:status

# Abrir consola interactiva de Laravel (Tinker)
docker exec -it laravel_app php artisan tinker

# Abrir consola MySQL
docker exec -it laravel_db mysql -u laravel -psecret laravel
```

---

## Tablas y migraciones (12)

| # | Tabla | Descripción |
| --- | --- | --- |
| 01 | `conferences` | AFC y NFC |
| 02 | `divisions` | 8 divisiones por conferencia |
| 03 | `stadiums` | Estadios con capacidad, superficie y tipo de techo |
| 04 | `teams` | 32 equipos con colores y año de fundación |
| 05 | `seasons` | Temporadas 2014–2023 con campeón del Super Bowl |
| 06 | `coaches` | Entrenadores por equipo y rol |
| 07 | `players` | Jugadores con posición, físico y estado activo |
| 08 | `games` | Partidos con marcador, semana y estadio |
| 09 | `player_game_stats` | Estadísticas individuales por jugador por partido |
| 10 | `contracts` | Contratos con valor total, salario anual y bono de firma |
| 11 | `injuries` | Lesiones con severidad y fecha de regreso |
| 12 | `draft_picks` | Selecciones de draft con ronda y pick global |

---

## Relaciones Eloquent implementadas

| Tipo | Modelo origen | Método | Modelo destino |
| --- | --- | --- | --- |
| `hasMany` | `Conference` | `divisions()` | `Division` |
| `hasManyThrough` | `Conference` | `teams()` | `Team` via `Division` |
| `belongsTo` | `Division` | `conference()` | `Conference` |
| `hasMany` | `Division` | `teams()` | `Team` |
| `belongsTo` | `Team` | `division()` | `Division` |
| `hasMany` | `Team` | `players()` | `Player` |
| `hasMany` | `Team` | `coaches()` | `Coach` |
| `hasMany` | `Team` | `homeGames()` | `Game` |
| `hasMany` | `Team` | `awayGames()` | `Game` |
| `belongsTo` | `Player` | `team()` | `Team` |
| `hasMany` | `Player` | `contracts()` | `Contract` |
| `hasOne` | `Player` | `activeContract()` | `Contract` |
| `hasMany` | `Player` | `injuries()` | `Injury` |
| `hasOne` | `Player` | `draftPick()` | `DraftPick` |
| `belongsToMany` | `Player` | `games()` | `Game` via `player_game_stats` |
| `belongsToMany` | `Game` | `players()` | `Player` via `player_game_stats` |
| `belongsTo` | `Game` | `season()` | `Season` |
| `belongsTo` | `Game` | `homeTeam()` | `Team` |
| `belongsTo` | `Game` | `awayTeam()` | `Team` |
| `hasMany` | `Season` | `games()` | `Game` |
| `belongsTo` | `Season` | `champion()` | `Team` |

---

## Datos sembrados

| Tabla | Registros |
| --- | ---: |
| conferences | 2 |
| divisions | 8 |
| stadiums | 32 |
| teams | 32 |
| seasons | 10 |
| coaches | 128 |
| players | 2 000 |
| games | 2 720 |
| player_game_stats | 16 320 |
| contracts | 700 |
| injuries | 500 |
| draft_picks | 800 |
| **Total** | **23 252** |

---

## Consultas Eloquent

Archivo: `src/app/Queries/FootballQueries.php`

Abrir Tinker y ejecutar:

```bash
docker exec -it laravel_app php artisan tinker
```

```php
use App\Queries\FootballQueries;

// 1. Eager Loading — equipos NFC con division, conferencia y roster activo
//    Usa with() para evitar N+1: sin él, 16 equipos × 3 accesos = 48 queries extra
FootballQueries::equiposNFCConRoster();

// 2. Top 10 QBs por yardas de pase en una temporada (withSum + filtro + orden)
FootballQueries::topQBsPorYardasDePase(2023);

// 3. Partidos con diferencia mayor a 21 puntos (whereRaw + relaciones)
FootballQueries::partidosPorGoleada(21);

// 4. Jugadores activos en lista de lesionados sin fecha de regreso (whereHas)
FootballQueries::jugadoresEnListaDeLesionados();

// 5. Contratos activos > $5M que vencen este año (filtro + orderByDesc)
FootballQueries::contratosProximosAVencer(2025);

// 6. Tabla de posiciones de una temporada (withCount + map + sortByDesc)
FootballQueries::tablaPosiciones(2023);
```

---

## Verificar datos desde MySQL

```bash
docker exec -it laravel_db mysql -u laravel -psecret laravel
```

```sql
-- Conteo por tabla
SELECT 'conferences' AS tabla, COUNT(*) AS total FROM conferences
UNION ALL SELECT 'teams',            COUNT(*) FROM teams
UNION ALL SELECT 'players',          COUNT(*) FROM players
UNION ALL SELECT 'games',            COUNT(*) FROM games
UNION ALL SELECT 'player_game_stats',COUNT(*) FROM player_game_stats;

-- Los 32 equipos con su división y conferencia
SELECT t.city, t.name, d.name AS division, c.name AS conference
FROM teams t
JOIN divisions d ON d.id = t.division_id
JOIN conferences c ON c.id = d.conference_id
ORDER BY c.name, d.name, t.name;

-- Top 5 QBs con más yardas de pase
SELECT CONCAT(p.first_name, ' ', p.last_name) AS jugador,
       SUM(s.passing_yards) AS yardas_pase
FROM players p
JOIN player_game_stats s ON s.player_id = p.id
WHERE p.position = 'QB'
GROUP BY p.id
ORDER BY yardas_pase DESC
LIMIT 5;
```

---

## Estructura del proyecto

```text
Ejercicio-7-DB/
├── Dockerfile
├── docker-compose.yml
├── docker/
│   └── nginx/
│       └── default.conf
├── README.md
└── src/
    ├── .env
    ├── artisan
    ├── composer.json
    ├── app/
    │   ├── Models/
    │   │   ├── Conference.php
    │   │   ├── Division.php
    │   │   ├── Stadium.php
    │   │   ├── Team.php
    │   │   ├── Season.php
    │   │   ├── Coach.php
    │   │   ├── Player.php
    │   │   ├── Game.php
    │   │   ├── PlayerGameStat.php
    │   │   ├── Contract.php
    │   │   ├── Injury.php
    │   │   └── DraftPick.php
    │   └── Queries/
    │       └── FootballQueries.php
    └── database/
        ├── migrations/
        │   ├── 2024_01_01_000001_create_conferences_table.php
        │   ├── 2024_01_01_000002_create_divisions_table.php
        │   ├── 2024_01_01_000003_create_stadiums_table.php
        │   ├── 2024_01_01_000004_create_teams_table.php
        │   ├── 2024_01_01_000005_create_seasons_table.php
        │   ├── 2024_01_01_000006_create_coaches_table.php
        │   ├── 2024_01_01_000007_create_players_table.php
        │   ├── 2024_01_01_000008_create_games_table.php
        │   ├── 2024_01_01_000009_create_player_game_stats_table.php
        │   ├── 2024_01_01_000010_create_contracts_table.php
        │   ├── 2024_01_01_000011_create_injuries_table.php
        │   └── 2024_01_01_000012_create_draft_picks_table.php
        └── seeders/
            └── DatabaseSeeder.php
```

---

## Apagar los contenedores

```bash
# Solo apagar
docker compose down

# Apagar y borrar el volumen de MySQL (elimina todos los datos)
docker compose down -v
```
