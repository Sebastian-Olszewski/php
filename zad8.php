<?php

declare(strict_types=1);

$rekordy = [
    ["id"=>1, "imie"=>"anna",     "wiek"=>"25",  "email"=>"anna@test.com",     "wynik"=>92.5],
    ["id"=>2, "imie"=>"Bartosz",  "wiek"=>"abc", "email"=>"bartosz@test.com",  "wynik"=>78.0],
    ["id"=>3, "imie"=>"celina",   "wiek"=>"31",  "email"=>"celina@test.com",   "wynik"=>105.0],
    ["id"=>4, "imie"=>"Dawid",    "wiek"=>"45",  "email"=>"",                  "wynik"=>66.5],
    ["id"=>5, "imie"=>"EWA",      "wiek"=>"28",  "email"=>"ewa@test.com",      "wynik"=>88.0],
    ["id"=>6, "imie"=>"filip",    "wiek"=>"130", "email"=>"filip@test.com",    "wynik"=>74.0],
    ["id"=>7, "imie"=>"Grażyna",  "wiek"=>"52",  "email"=>"anna@test.com",     "wynik"=>91.0],
    ["id"=>8, "imie"=>"Henryk",   "wiek"=>"19",  "email"=>"henryk@test.com",   "wynik"=>-5.0],
    ["id"=>9, "imie"=>"irena",    "wiek"=>"37",  "email"=>"irena@test.com",    "wynik"=>83.5],
    ["id"=>10, "imie"=>"JANEK",   "wiek"=>"22",  "email"=>"janek@test.com",    "wynik"=>55.0],
    ["id"=>11, "imie"=>"Kasia",   "wiek"=>"29",  "email"=>"kasia@test.com",    "wynik"=>97.0],
    ["id"=>12, "imie"=>"Leon",    "wiek"=>"41",  "email"=>"leon@test.com",     "wynik"=>62.0],
    ["id"=>13, "imie"=>"Marta",   "wiek"=>"0",   "email"=>"marta@test.com",    "wynik"=>79.5],
    ["id"=>14, "imie"=>"norbert", "wiek"=>"33",  "email"=>"norbert@test.com",  "wynik"=>86.0],
    ["id"=>15, "imie"=>"Ola",     "wiek"=>"26",  "email"=>"ola@test.com",      "wynik"=>91.0],
];

function normalizujImie(string $imie): string
{
    $imie = mb_strtolower(trim($imie), 'UTF-8');
    return mb_strtoupper(mb_substr($imie, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($imie, 1, null, 'UTF-8');
}

function waliduj(array $dane): array
{
    $valid = [];
    $rejected = [];

    foreach ($dane as $rekord) {
        $wiekRaw = (string)$rekord['wiek'];
        $wynikRaw = $rekord['wynik'];
        $email = trim((string)$rekord['email']);

        if (filter_var($wiekRaw, FILTER_VALIDATE_INT) === false || (int)$wiekRaw < 1 || (int)$wiekRaw > 120) {
            $rejected[] = [
                'id' => $rekord['id'],
                'imie' => $rekord['imie'],
                'powod' => "nieprawidłowy wiek '{$wiekRaw}'",
            ];
            continue;
        }

        if (!is_numeric($wynikRaw) || (float)$wynikRaw < 0.0 || (float)$wynikRaw > 100.0) {
            $rejected[] = [
                'id' => $rekord['id'],
                'imie' => $rekord['imie'],
                'powod' => "wynik poza zakresem [0-100]: " . number_format((float)$wynikRaw, 1, '.', ''),
            ];
            continue;
        }

        if ($email === '') {
            $rejected[] = [
                'id' => $rekord['id'],
                'imie' => $rekord['imie'],
                'powod' => "pusty email",
            ];
            continue;
        }

        $valid[] = $rekord;
    }

    return ['valid' => $valid, 'rejected' => $rejected];
}

function transformuj(array $dane): array
{
    $valid = [];
    $rejected = [];
    $seen = [];

    foreach ($dane as $rekord) {
        $email = trim((string)$rekord['email']);
        $emailKey = mb_strtolower($email, 'UTF-8');

        if (isset($seen[$emailKey])) {
            $rejected[] = [
                'id' => $rekord['id'],
                'imie' => $rekord['imie'],
                'powod' => "duplikat email '{$email}'",
            ];
            continue;
        }

        $seen[$emailKey] = true;

        $valid[] = [
            'id' => $rekord['id'],
            'imie' => normalizujImie((string)$rekord['imie']),
            'wiek' => (int)$rekord['wiek'],
            'email' => $email,
            'wynik' => (float)$rekord['wynik'],
        ];
    }

    return ['valid' => $valid, 'rejected' => $rejected];
}

function ocenaLiterowa(float $wynik): string
{
    if ($wynik >= 90.0) {
        return 'A';
    }
    if ($wynik >= 75.0) {
        return 'B';
    }
    if ($wynik >= 60.0) {
        return 'C';
    }
    return 'D';
}

function statystykiOcen(array $rekordy): array
{
    $grupy = [
        'A' => ['count' => 0, 'sum' => 0.0],
        'B' => ['count' => 0, 'sum' => 0.0],
        'C' => ['count' => 0, 'sum' => 0.0],
        'D' => ['count' => 0, 'sum' => 0.0],
    ];

    foreach ($rekordy as $rekord) {
        $ocena = $rekord['ocena'];
        $grupy[$ocena]['count']++;
        $grupy[$ocena]['sum'] += $rekord['wynik'];
    }

    foreach ($grupy as $ocena => $dane) {
        $grupy[$ocena]['avg'] = $dane['count'] > 0 ? $dane['sum'] / $dane['count'] : 0.0;
    }

    return $grupy;
}

$etapE = waliduj($rekordy);
$etapT = transformuj($etapE['valid']);

$odrzucone = array_merge($etapE['rejected'], $etapT['rejected']);

usort($odrzucone, function (array $a, array $b): int {
    return $a['id'] <=> $b['id'];
});

$finalnaBaza = [];

foreach ($etapT['valid'] as $rekord) {
    $rekord['ocena'] = ocenaLiterowa($rekord['wynik']);
    $finalnaBaza[] = $rekord;
}

$statystyki = statystykiOcen($finalnaBaza);

echo "=== Etap E: Walidacja ===" . PHP_EOL;
echo "Odrzucone rekordy (" . count($odrzucone) . "):" . PHP_EOL;

foreach ($odrzucone as $r) {
    echo "  - ID " . $r['id'] . " (" . $r['imie'] . "): " . $r['powod'] . PHP_EOL;
}

echo PHP_EOL;
echo "=== Etap L: Finalna baza (" . count($finalnaBaza) . " rekordów) ===" . PHP_EOL;

printf("%-12s | %4s | %-22s | %5s | %s\n", "Imię", "Wiek", "Email", "Wynik", "Ocena");
echo str_repeat('-', 12) . "-|-" . str_repeat('-', 4) . "-|-" . str_repeat('-', 22) . "-|-" . str_repeat('-', 5) . "-|-" . str_repeat('-', 5) . PHP_EOL;

foreach ($finalnaBaza as $r) {
    printf(
        "%-12s | %4d | %-22s | %5.1f | %s\n",
        $r['imie'],
        $r['wiek'],
        $r['email'],
        $r['wynik'],
        $r['ocena']
    );
}

echo PHP_EOL;
echo "Rozkład ocen:" . PHP_EOL;

foreach (['A', 'B', 'C', 'D'] as $ocena) {
    if ($statystyki[$ocena]['count'] > 0) {
        echo "  {$ocena}: " . $statystyki[$ocena]['count'] . " studentów, średnia: " . number_format($statystyki[$ocena]['avg'], 1, '.', '') . "%" . PHP_EOL;
    }
}
