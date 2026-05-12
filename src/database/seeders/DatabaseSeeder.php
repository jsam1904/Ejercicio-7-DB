<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder principal de la base de datos de Fútbol Americano NFL.
 *
 * Registros generados (total > 10,000):
 *  - conferences      :     2
 *  - divisions        :     8
 *  - stadiums         :    32
 *  - teams            :    32
 *  - seasons          :    10
 *  - coaches          :   128  (4 por equipo)
 *  - players          : 2,000  (55 por equipo + 240 agentes libres)
 *  - games            : 2,720  (17 semanas × 16 partidos × 10 temporadas)
 *  - player_game_stats:16,320  (6 por partido: 2 QBs + 2 habilidad + 2 defensa)
 *  - contracts        :   700
 *  - injuries         :   500
 *  - draft_picks      :   800
 *  ─────────────────────────────
 *  TOTAL              :23,252
 */
class DatabaseSeeder extends Seeder
{
    private string $now;
    private array $divisionIds = []; // "AFC East" => id
    private array $teamIds     = []; // "KC" => id
    private array $seasonIds   = []; // 2023 => id

    private const POSITIONS = [
        'QB', 'QB', 'QB',
        'RB', 'RB', 'RB', 'RB', 'FB',
        'WR', 'WR', 'WR', 'WR', 'WR', 'WR',
        'TE', 'TE', 'TE',
        'OT', 'OT', 'OT', 'OT',
        'OG', 'OG', 'OG', 'OG',
        'C', 'C',
        'DE', 'DE', 'DE', 'DE',
        'DT', 'DT', 'DT', 'NT',
        'OLB', 'OLB', 'OLB', 'OLB',
        'ILB', 'ILB', 'MLB', 'MLB',
        'CB', 'CB', 'CB', 'CB', 'CB',
        'FS', 'FS', 'SS', 'SS',
        'K', 'P', 'LS',
    ]; // 55 slots por equipo

    /**
     * [division, stadium_name, stad_city, stad_state, capacity, surface, roof, year_opened,
     *  team_city, team_name, abbreviation, founded_year, primary_color, secondary_color]
     */
    private const TEAMS_DATA = [
        // ── AFC East ────────────────────────────────────────────────────────
        ['AFC East','Highmark Stadium','Orchard Park','NY',71608,'grass','open',1973,
         'Buffalo','Bills','BUF',1960,'#00338D','#C60C30'],
        ['AFC East','Hard Rock Stadium','Miami Gardens','FL',64767,'grass','open',1987,
         'Miami','Dolphins','MIA',1966,'#008E97','#FC4C02'],
        ['AFC East','Gillette Stadium','Foxborough','MA',65878,'field_turf','open',2002,
         'New England','Patriots','NE',1960,'#002244','#C60C30'],
        ['AFC East','MetLife Stadium (Jets)','East Rutherford','NJ',82500,'field_turf','open',2010,
         'New York','Jets','NYJ',1960,'#125740','#FFFFFF'],
        // ── AFC North ───────────────────────────────────────────────────────
        ['AFC North','M&T Bank Stadium','Baltimore','MD',71008,'field_turf','open',1998,
         'Baltimore','Ravens','BAL',1996,'#241773','#9E7C0C'],
        ['AFC North','Paycor Stadium','Cincinnati','OH',65515,'artificial_turf','open',2000,
         'Cincinnati','Bengals','CIN',1968,'#FB4F14','#000000'],
        ['AFC North','Huntington Bank Field','Cleveland','OH',67431,'grass','open',1999,
         'Cleveland','Browns','CLE',1946,'#311D00','#FF3C00'],
        ['AFC North','Acrisure Stadium','Pittsburgh','PA',68400,'grass','open',2001,
         'Pittsburgh','Steelers','PIT',1933,'#FFB612','#101820'],
        // ── AFC South ───────────────────────────────────────────────────────
        ['AFC South','NRG Stadium','Houston','TX',72220,'grass','retractable',2002,
         'Houston','Texans','HOU',2002,'#03202F','#A71930'],
        ['AFC South','Lucas Oil Stadium','Indianapolis','IN',67000,'artificial_turf','retractable',2008,
         'Indianapolis','Colts','IND',1953,'#002C5F','#A2AAAD'],
        ['AFC South','EverBank Stadium','Jacksonville','FL',62000,'grass','open',1995,
         'Jacksonville','Jaguars','JAX',1995,'#006778','#D7A22A'],
        ['AFC South','Nissan Stadium','Nashville','TN',69143,'artificial_turf','open',1999,
         'Tennessee','Titans','TEN',1960,'#0C2340','#4B92DB'],
        // ── AFC West ────────────────────────────────────────────────────────
        ['AFC West','Empower Field at Mile High','Denver','CO',76125,'grass','open',2001,
         'Denver','Broncos','DEN',1960,'#FB4F14','#002244'],
        ['AFC West','Arrowhead Stadium','Kansas City','MO',76416,'grass','open',1972,
         'Kansas City','Chiefs','KC',1960,'#E31837','#FFB81C'],
        ['AFC West','Allegiant Stadium','Las Vegas','NV',65000,'field_turf','dome',2020,
         'Las Vegas','Raiders','LV',1960,'#000000','#A5ACAF'],
        ['AFC West','SoFi Stadium (Chargers)','Inglewood','CA',70240,'field_turf','open',2020,
         'Los Angeles','Chargers','LAC',1960,'#0080C6','#FFC20E'],
        // ── NFC East ────────────────────────────────────────────────────────
        ['NFC East','AT&T Stadium','Arlington','TX',80000,'field_turf','retractable',2009,
         'Dallas','Cowboys','DAL',1960,'#003594','#041E42'],
        ['NFC East','MetLife Stadium (Giants)','East Rutherford','NJ',82500,'field_turf','open',2010,
         'New York','Giants','NYG',1925,'#0B2265','#A71930'],
        ['NFC East','Lincoln Financial Field','Philadelphia','PA',69796,'grass','open',2003,
         'Philadelphia','Eagles','PHI',1933,'#004C54','#A5ACAF'],
        ['NFC East','Northwest Stadium','Landover','MD',82000,'grass','open',1997,
         'Washington','Commanders','WAS',1932,'#5A1414','#FFB612'],
        // ── NFC North ───────────────────────────────────────────────────────
        ['NFC North','Soldier Field','Chicago','IL',61500,'grass','open',1924,
         'Chicago','Bears','CHI',1919,'#0B162A','#C83803'],
        ['NFC North','Ford Field','Detroit','MI',65000,'field_turf','dome',2002,
         'Detroit','Lions','DET',1930,'#0076B6','#B0B7BC'],
        ['NFC North','Lambeau Field','Green Bay','WI',81441,'grass','open',1957,
         'Green Bay','Packers','GB',1919,'#203731','#FFB612'],
        ['NFC North','U.S. Bank Stadium','Minneapolis','MN',66860,'field_turf','dome',2016,
         'Minnesota','Vikings','MIN',1960,'#4F2683','#FFC62F'],
        // ── NFC South ───────────────────────────────────────────────────────
        ['NFC South','Mercedes-Benz Stadium','Atlanta','GA',71000,'field_turf','retractable',2017,
         'Atlanta','Falcons','ATL',1966,'#A71930','#000000'],
        ['NFC South','Bank of America Stadium','Charlotte','NC',74455,'grass','open',1996,
         'Carolina','Panthers','CAR',1995,'#0085CA','#101820'],
        ['NFC South','Caesars Superdome','New Orleans','LA',73208,'field_turf','dome',1975,
         'New Orleans','Saints','NO',1967,'#D3BC8D','#101820'],
        ['NFC South','Raymond James Stadium','Tampa','FL',65618,'grass','open',1998,
         'Tampa Bay','Buccaneers','TB',1976,'#D50A0A','#FF7900'],
        // ── NFC West ────────────────────────────────────────────────────────
        ['NFC West','State Farm Stadium','Glendale','AZ',63400,'grass','retractable',2006,
         'Arizona','Cardinals','ARI',1898,'#97233F','#000000'],
        ['NFC West','SoFi Stadium (Rams)','Inglewood','CA',70240,'field_turf','open',2020,
         'Los Angeles','Rams','LAR',1936,'#003594','#FFA300'],
        ['NFC West',"Levi's Stadium",'Santa Clara','CA',68500,'grass','open',2014,
         'San Francisco','49ers','SF',1946,'#AA0000','#B3995D'],
        ['NFC West','Lumen Field','Seattle','WA',69000,'field_turf','open',2002,
         'Seattle','Seahawks','SEA',1976,'#002244','#69BE28'],
    ];

    /** Temporadas 2014-2023 con campeón real del Super Bowl */
    private const SEASONS_DATA = [
        ['year'=>2014,'start'=>'2014-09-04','end'=>'2015-02-01','champion'=>'NE'],
        ['year'=>2015,'start'=>'2015-09-10','end'=>'2016-02-07','champion'=>'DEN'],
        ['year'=>2016,'start'=>'2016-09-08','end'=>'2017-02-05','champion'=>'NE'],
        ['year'=>2017,'start'=>'2017-09-07','end'=>'2018-02-04','champion'=>'PHI'],
        ['year'=>2018,'start'=>'2018-09-06','end'=>'2019-02-03','champion'=>'NE'],
        ['year'=>2019,'start'=>'2019-09-05','end'=>'2020-02-02','champion'=>'KC'],
        ['year'=>2020,'start'=>'2020-09-10','end'=>'2021-02-07','champion'=>'TB'],
        ['year'=>2021,'start'=>'2021-09-09','end'=>'2022-02-13','champion'=>'LAR'],
        ['year'=>2022,'start'=>'2022-09-08','end'=>'2023-02-12','champion'=>'KC'],
        ['year'=>2023,'start'=>'2023-09-07','end'=>'2024-02-11','champion'=>'KC'],
    ];

    // =========================================================================

    public function run(): void
    {
        $this->now = Carbon::now()->toDateTimeString();

        $this->command->info('Sembrando conferencias y divisiones...');
        $this->seedConferencesAndDivisions();

        $this->command->info('Sembrando estadios y equipos...');
        $this->seedStadiumsAndTeams();

        $this->command->info('Sembrando temporadas...');
        $this->seedSeasons();

        $this->command->info('Sembrando entrenadores (128)...');
        $this->seedCoaches();

        $this->command->info('Sembrando jugadores (~2,000)...');
        $this->seedPlayers();

        $this->command->info('Sembrando partidos (2,720)...');
        $this->seedGames();

        $this->command->info('Sembrando estadísticas por partido (~16,320)...');
        $this->seedPlayerGameStats();

        $this->command->info('Sembrando contratos (700)...');
        $this->seedContracts();

        $this->command->info('Sembrando lesiones (500)...');
        $this->seedInjuries();

        $this->command->info('Sembrando selecciones de draft (800)...');
        $this->seedDraftPicks();

        $this->command->info('¡Base de datos sembrada exitosamente!');
    }

    // =========================================================================
    // Métodos de siembra
    // =========================================================================

    private function seedConferencesAndDivisions(): void
    {
        $afcId = DB::table('conferences')->insertGetId([
            'name' => 'AFC', 'full_name' => 'American Football Conference',
            'created_at' => $this->now, 'updated_at' => $this->now,
        ]);
        $nfcId = DB::table('conferences')->insertGetId([
            'name' => 'NFC', 'full_name' => 'National Football Conference',
            'created_at' => $this->now, 'updated_at' => $this->now,
        ]);

        $divisionsMap = [
            [$afcId, 'AFC', 'East'], [$afcId, 'AFC', 'North'],
            [$afcId, 'AFC', 'South'], [$afcId, 'AFC', 'West'],
            [$nfcId, 'NFC', 'East'], [$nfcId, 'NFC', 'North'],
            [$nfcId, 'NFC', 'South'], [$nfcId, 'NFC', 'West'],
        ];

        foreach ($divisionsMap as [$confId, $conf, $divName]) {
            $id = DB::table('divisions')->insertGetId([
                'conference_id' => $confId,
                'name'          => $divName,
                'created_at'    => $this->now,
                'updated_at'    => $this->now,
            ]);
            $this->divisionIds["{$conf} {$divName}"] = $id;
        }
    }

    private function seedStadiumsAndTeams(): void
    {
        foreach (self::TEAMS_DATA as $row) {
            [
                $divName, $stadName, $stadCity, $stadState, $capacity,
                $surface, $roof, $yearOpened,
                $teamCity, $teamName, $abbr, $founded, $primary, $secondary,
            ] = $row;

            $stadiumId = DB::table('stadiums')->insertGetId([
                'name'        => $stadName,
                'city'        => $stadCity,
                'state'       => $stadState,
                'capacity'    => $capacity,
                'surface'     => $surface,
                'roof_type'   => $roof,
                'year_opened' => $yearOpened,
                'created_at'  => $this->now,
                'updated_at'  => $this->now,
            ]);

            $teamId = DB::table('teams')->insertGetId([
                'division_id'     => $this->divisionIds[$divName],
                'stadium_id'      => $stadiumId,
                'name'            => $teamName,
                'city'            => $teamCity,
                'abbreviation'    => $abbr,
                'primary_color'   => $primary,
                'secondary_color' => $secondary,
                'founded_year'    => $founded,
                'created_at'      => $this->now,
                'updated_at'      => $this->now,
            ]);

            $this->teamIds[$abbr] = $teamId;
        }
    }

    private function seedSeasons(): void
    {
        foreach (self::SEASONS_DATA as $data) {
            $id = DB::table('seasons')->insertGetId([
                'year'             => $data['year'],
                'start_date'       => $data['start'],
                'end_date'         => $data['end'],
                'champion_team_id' => $this->teamIds[$data['champion']] ?? null,
                'created_at'       => $this->now,
                'updated_at'       => $this->now,
            ]);
            $this->seasonIds[$data['year']] = $id;
        }
    }

    private function seedCoaches(): void
    {
        $faker = fake();
        $roles = [
            'head_coach', 'offensive_coordinator',
            'defensive_coordinator', 'quarterbacks_coach',
        ];
        $coaches = [];

        foreach ($this->teamIds as $teamId) {
            foreach ($roles as $role) {
                $coaches[] = [
                    'team_id'    => $teamId,
                    'first_name' => $faker->firstName('male'),
                    'last_name'  => $faker->lastName(),
                    'role'       => $role,
                    'hire_date'  => $faker->dateTimeBetween('-6 years', 'now')->format('Y-m-d'),
                    'is_active'  => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }
        }

        DB::table('coaches')->insert($coaches); // 128 registros en 1 query
    }

    private function seedPlayers(): void
    {
        $faker = fake();
        $colleges = [
            'Alabama','Georgia','Ohio State','Clemson','LSU','Michigan',
            'Notre Dame','Oklahoma','Penn State','Florida','Texas','USC',
            'Florida State','Oregon','Washington','Auburn','Iowa','Wisconsin',
            'Tennessee','Stanford','UCF','Baylor','TCU','Ole Miss',
        ];

        $players = [];

        // 55 jugadores activos por cada uno de los 32 equipos = 1,760
        foreach ($this->teamIds as $teamId) {
            foreach (self::POSITIONS as $position) {
                $players[] = [
                    'team_id'       => $teamId,
                    'first_name'    => $faker->firstName('male'),
                    'last_name'     => $faker->lastName(),
                    'position'      => $position,
                    'jersey_number' => mt_rand(1, 99),
                    'height_inches' => $this->heightByPosition($position),
                    'weight_lbs'    => $this->weightByPosition($position),
                    'date_of_birth' => $faker->dateTimeBetween('-36 years', '-21 years')->format('Y-m-d'),
                    'college'       => $colleges[array_rand($colleges)],
                    'is_active'     => 1,
                    'created_at'    => $this->now,
                    'updated_at'    => $this->now,
                ];

                if (count($players) >= 500) {
                    DB::table('players')->insert($players);
                    $players = [];
                }
            }
        }

        // 240 agentes libres / jugadores retirados (sin equipo)
        for ($i = 0; $i < 240; $i++) {
            $pos = self::POSITIONS[array_rand(self::POSITIONS)];
            $players[] = [
                'team_id'       => null,
                'first_name'    => $faker->firstName('male'),
                'last_name'     => $faker->lastName(),
                'position'      => $pos,
                'jersey_number' => null,
                'height_inches' => $this->heightByPosition($pos),
                'weight_lbs'    => $this->weightByPosition($pos),
                'date_of_birth' => $faker->dateTimeBetween('-42 years', '-22 years')->format('Y-m-d'),
                'college'       => $colleges[array_rand($colleges)],
                'is_active'     => 0,
                'created_at'    => $this->now,
                'updated_at'    => $this->now,
            ];
        }

        if (!empty($players)) {
            DB::table('players')->insert($players);
        }
    }

    private function seedGames(): void
    {
        $teamIds = array_values($this->teamIds);

        // Pre-carga la relación equipo→estadio para no hacer una query por partido
        $teamStadiums = DB::table('teams')->pluck('stadium_id', 'id')->toArray();

        $games = [];

        foreach (self::SEASONS_DATA as $seasonData) {
            $seasonId    = $this->seasonIds[$seasonData['year']];
            $seasonStart = Carbon::parse($seasonData['start']);

            // 17 semanas × 16 partidos = 272 partidos por temporada
            for ($week = 1; $week <= 17; $week++) {
                $shuffled = $teamIds;
                shuffle($shuffled); // emparejamiento aleatorio cada semana

                $gameDate = (clone $seasonStart)->addWeeks($week - 1)->format('Y-m-d') . ' 13:00:00';

                for ($i = 0; $i < 32; $i += 2) {
                    $homeId    = $shuffled[$i];
                    $awayId    = $shuffled[$i + 1];
                    $homeScore = mt_rand(0, 45);
                    $awayScore = mt_rand(0, 45);

                    // Ventaja de local: 55% victorias históricas
                    if (mt_rand(1, 100) <= 55 && $homeScore < $awayScore) {
                        [$homeScore, $awayScore] = [$awayScore, $homeScore];
                    }

                    $games[] = [
                        'season_id'    => $seasonId,
                        'week'         => $week,
                        'home_team_id' => $homeId,
                        'away_team_id' => $awayId,
                        'stadium_id'   => $teamStadiums[$homeId],
                        'home_score'   => $homeScore,
                        'away_score'   => $awayScore,
                        'game_date'    => $gameDate,
                        'is_playoff'   => 0,
                        'created_at'   => $this->now,
                        'updated_at'   => $this->now,
                    ];
                }

                if (count($games) >= 500) {
                    DB::table('games')->insert($games);
                    $games = [];
                }
            }
        }

        if (!empty($games)) {
            DB::table('games')->insert($games);
        }
    }

    private function seedPlayerGameStats(): void
    {
        // Agrupa jugadores por equipo y categoría de posición para asignar stats coherentes
        $allPlayers = DB::table('players')
            ->select('id', 'team_id', 'position')
            ->whereNotNull('team_id')
            ->get();

        $qbsByTeam     = []; // solo QBs
        $skillByTeam   = []; // RB, FB, WR, TE
        $defenseByTeam = []; // DE, DT, NT, OLB, ILB, MLB, CB, FS, SS

        foreach ($allPlayers as $p) {
            if ($p->position === 'QB') {
                $qbsByTeam[$p->team_id][] = $p->id;
            } elseif (in_array($p->position, ['RB','FB','WR','TE'])) {
                $skillByTeam[$p->team_id][] = $p->id;
            } elseif (in_array($p->position, ['DE','DT','NT','OLB','ILB','MLB','CB','FS','SS'])) {
                $defenseByTeam[$p->team_id][] = $p->id;
            }
        }

        $games = DB::table('games')->select('id', 'home_team_id', 'away_team_id')->get();

        $stats = [];

        foreach ($games as $game) {
            foreach ([$game->home_team_id, $game->away_team_id] as $teamId) {

                // ── 1 QB: estadísticas de pase ──────────────────────────────
                if (!empty($qbsByTeam[$teamId])) {
                    $qbId   = $qbsByTeam[$teamId][array_rand($qbsByTeam[$teamId])];
                    $stats[] = [
                        'player_id'            => $qbId,
                        'game_id'              => $game->id,
                        'passing_yards'        => mt_rand(160, 420),
                        'passing_touchdowns'   => mt_rand(0, 4),
                        'interceptions_thrown' => mt_rand(0, 3),
                        'rushing_yards'        => mt_rand(-5, 35),
                        'rushing_touchdowns'   => (mt_rand(1, 10) === 1) ? 1 : 0,
                        'receptions'           => 0,
                        'receiving_yards'      => 0,
                        'receiving_touchdowns' => 0,
                        'tackles'              => 0,
                        'sacks'                => 0.0,
                        'created_at'           => $this->now,
                        'updated_at'           => $this->now,
                    ];
                }

                // ── 1 jugador de habilidad: carrera o recepción ─────────────
                if (!empty($skillByTeam[$teamId])) {
                    $skillId   = $skillByTeam[$teamId][array_rand($skillByTeam[$teamId])];
                    $isRunner  = mt_rand(0, 1);
                    $stats[] = [
                        'player_id'            => $skillId,
                        'game_id'              => $game->id,
                        'passing_yards'        => 0,
                        'passing_touchdowns'   => 0,
                        'interceptions_thrown' => 0,
                        'rushing_yards'        => $isRunner ? mt_rand(25, 150) : mt_rand(0, 20),
                        'rushing_touchdowns'   => (mt_rand(1, 6) === 1) ? 1 : 0,
                        'receptions'           => $isRunner ? mt_rand(0, 4) : mt_rand(3, 12),
                        'receiving_yards'      => $isRunner ? mt_rand(0, 40) : mt_rand(30, 180),
                        'receiving_touchdowns' => (mt_rand(1, 8) === 1) ? 1 : 0,
                        'tackles'              => 0,
                        'sacks'                => 0.0,
                        'created_at'           => $this->now,
                        'updated_at'           => $this->now,
                    ];
                }

                // ── 1 jugador defensivo: tacleadas y sacks ──────────────────
                if (!empty($defenseByTeam[$teamId])) {
                    $defId = $defenseByTeam[$teamId][array_rand($defenseByTeam[$teamId])];
                    $stats[] = [
                        'player_id'            => $defId,
                        'game_id'              => $game->id,
                        'passing_yards'        => 0,
                        'passing_touchdowns'   => 0,
                        'interceptions_thrown' => 0,
                        'rushing_yards'        => 0,
                        'rushing_touchdowns'   => 0,
                        'receptions'           => 0,
                        'receiving_yards'      => 0,
                        'receiving_touchdowns' => 0,
                        'tackles'              => mt_rand(2, 14),
                        'sacks'                => (float)(mt_rand(0, 4) * 0.5),
                        'created_at'           => $this->now,
                        'updated_at'           => $this->now,
                    ];
                }
            }

            if (count($stats) >= 500) {
                DB::table('player_game_stats')->insert($stats);
                $stats = [];
            }
        }

        if (!empty($stats)) {
            DB::table('player_game_stats')->insert($stats);
        }
    }

    private function seedContracts(): void
    {
        $players = DB::table('players')
            ->whereNotNull('team_id')
            ->where('is_active', 1)
            ->inRandomOrder()
            ->limit(700)
            ->get(['id', 'team_id', 'position']);

        $contracts = [];
        foreach ($players as $player) {
            $startYear = mt_rand(2018, 2022);
            $years     = mt_rand(1, 5);
            $endYear   = $startYear + $years;
            $salary    = $this->salaryByPosition($player->position);
            $bonus     = (int)($salary * mt_rand(5, 30) / 100);

            $contracts[] = [
                'player_id'     => $player->id,
                'team_id'       => $player->team_id,
                'start_year'    => $startYear,
                'end_year'      => $endYear,
                'total_value'   => $salary * $years,
                'annual_salary' => $salary,
                'signing_bonus' => $bonus,
                'is_active'     => ($endYear >= 2024) ? 1 : 0,
                'created_at'    => $this->now,
                'updated_at'    => $this->now,
            ];
        }

        DB::table('contracts')->insert($contracts);
    }

    private function seedInjuries(): void
    {
        $faker      = fake();
        $injTypes   = [
            'ACL Tear','MCL Sprain','Hamstring Strain','Shoulder Dislocation',
            'Ankle Sprain','Concussion','Rib Fracture','Knee Contusion',
            'Quad Strain','Achilles Tendon Tear','Rotator Cuff Tear',
            'Hip Flexor Strain','Groin Strain','Turf Toe','Hand Fracture',
        ];
        $bodyParts  = [
            'knee','shoulder','ankle','hamstring','head','ribs',
            'quadriceps','achilles','hip','groin','toe','hand','back',
        ];
        $severities = ['minor','minor','moderate','moderate','severe'];

        $playerIds = DB::table('players')
            ->whereNotNull('team_id')
            ->inRandomOrder()
            ->limit(500)
            ->pluck('id');

        $injuries = [];
        foreach ($playerIds as $playerId) {
            $injuryDate  = $faker->dateTimeBetween('-3 years', '-1 month');
            $severity    = $severities[array_rand($severities)];
            $gamesMissed = match ($severity) {
                'minor'    => mt_rand(0, 2),
                'moderate' => mt_rand(2, 6),
                'severe'   => mt_rand(4, 16),
                default    => 0,
            };
            // 30% de lesiones sin fecha de regreso (aún activas)
            $returnDate = (mt_rand(1, 10) > 3)
                ? $faker->dateTimeBetween($injuryDate, 'now')->format('Y-m-d')
                : null;

            $injuries[] = [
                'player_id'    => $playerId,
                'injury_type'  => $injTypes[array_rand($injTypes)],
                'body_part'    => $bodyParts[array_rand($bodyParts)],
                'severity'     => $severity,
                'injury_date'  => $injuryDate->format('Y-m-d'),
                'return_date'  => $returnDate,
                'games_missed' => $gamesMissed,
                'created_at'   => $this->now,
                'updated_at'   => $this->now,
            ];
        }

        DB::table('injuries')->insert($injuries);
    }

    private function seedDraftPicks(): void
    {
        $players     = DB::table('players')->inRandomOrder()->limit(800)->get(['id','team_id']);
        $teamIdsList = array_values($this->teamIds);
        $picks       = [];

        foreach ($players as $index => $player) {
            // 7 rondas × 32 picks = 224 selecciones por año de draft
            $yearOffset  = (int)floor($index / 224);
            $draftYear   = min(2014 + $yearOffset, 2023);
            $withinYear  = $index % 224;
            $round       = min((int)floor($withinYear / 32) + 1, 7);
            $pickNumber  = ($withinYear % 32) + 1;
            $overallPick = $withinYear + 1;

            $picks[] = [
                'player_id'    => $player->id,
                'team_id'      => $player->team_id ?? $teamIdsList[array_rand($teamIdsList)],
                'draft_year'   => $draftYear,
                'round'        => $round,
                'pick_number'  => $pickNumber,
                'overall_pick' => $overallPick,
                'created_at'   => $this->now,
                'updated_at'   => $this->now,
            ];
        }

        DB::table('draft_picks')->insert($picks);
    }

    // =========================================================================
    // Helpers de datos físicos y salariales realistas
    // =========================================================================

    private function heightByPosition(string $position): int
    {
        return match (true) {
            in_array($position, ['QB','RB','FB','K','P','LS'])        => mt_rand(70, 77),
            in_array($position, ['WR','CB','FS','SS'])                => mt_rand(68, 75),
            in_array($position, ['TE','OLB','ILB','MLB'])             => mt_rand(73, 79),
            in_array($position, ['OT','OG','C'])                      => mt_rand(75, 79),
            in_array($position, ['DE','DT','NT'])                     => mt_rand(73, 79),
            default                                                    => mt_rand(70, 76),
        };
    }

    private function weightByPosition(string $position): int
    {
        return match (true) {
            $position === 'QB'                                         => mt_rand(215, 245),
            in_array($position, ['RB','FB'])                          => mt_rand(200, 235),
            in_array($position, ['WR','CB','FS'])                     => mt_rand(175, 210),
            $position === 'SS'                                         => mt_rand(200, 225),
            in_array($position, ['TE','OLB'])                         => mt_rand(240, 265),
            in_array($position, ['ILB','MLB'])                        => mt_rand(235, 255),
            in_array($position, ['OT','OG','C'])                      => mt_rand(300, 340),
            in_array($position, ['DE','DT','NT'])                     => mt_rand(265, 330),
            in_array($position, ['K','P','LS'])                       => mt_rand(185, 215),
            default                                                    => mt_rand(220, 260),
        };
    }

    private function salaryByPosition(string $position): int
    {
        return match (true) {
            $position === 'QB'                                         => mt_rand(5_000_000,  45_000_000),
            in_array($position, ['WR','OT','DE'])                     => mt_rand(2_000_000,  20_000_000),
            in_array($position, ['RB','TE','CB','OG'])                => mt_rand(1_500_000,  15_000_000),
            in_array($position, ['ILB','OLB','MLB','FS','SS'])        => mt_rand(1_000_000,  12_000_000),
            in_array($position, ['DT','NT','C','FB'])                 => mt_rand(800_000,     8_000_000),
            default                                                    => mt_rand(700_000,     5_000_000),
        };
    }
}
