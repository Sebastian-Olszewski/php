<?php

declare(strict_types=1);

$zadania = [
    ["id"=>1, "nazwa"=>"T01", "start"=>480, "koniec"=>600],
    ["id"=>2, "nazwa"=>"T02", "start"=>510, "koniec"=>720],
    ["id"=>3, "nazwa"=>"T03", "start"=>540, "koniec"=>660],
    ["id"=>4, "nazwa"=>"T04", "start"=>600, "koniec"=>690],
    ["id"=>5, "nazwa"=>"T05", "start"=>660, "koniec"=>780],
    ["id"=>6, "nazwa"=>"T06", "start"=>690, "koniec"=>840],
    ["id"=>7, "nazwa"=>"T07", "start"=>720, "koniec"=>810],
    ["id"=>8, "nazwa"=>"T08", "start"=>780, "koniec"=>900],
    ["id"=>9, "nazwa"=>"T09", "start"=>840, "koniec"=>960],
    ["id"=>10, "nazwa"=>"T10", "start"=>480, "koniec"=>540],
    ["id"=>11, "nazwa"=>"T11", "start"=>570, "koniec"=>630],
    ["id"=>12, "nazwa"=>"T12", "start"=>750, "koniec"=>870],
    ["id"=>13, "nazwa"=>"T13", "start"=>900, "koniec"=>990],
    ["id"=>14, "nazwa"=>"T14", "start"=>495, "koniec"=>555],
    ["id"=>15, "nazwa"=>"T15", "start"=>870, "koniec"=>930],
];

function minutyNaCzas(int $m): string
{
    $h = intdiv($m, 60);
    $min = $m % 60;
    return $h . ':' . str_pad((string)$min, 2, '0', STR_PAD_LEFT);
}

function formatujZadanie(array $zadanie): string
{
    return $zadanie['nazwa'] . '(' . minutyNaCzas($zadanie['start']) . '-' . minutyNaCzas($zadanie['koniec']) . ')';
}

function koliduja(array $a, array $b): bool
{
    return max($a['start'], $b['start']) < min($a['koniec'], $b['koniec']);
}

function greedyJednaSala(array $zadania): array
{
    $posortowane = $zadania;

    usort($posortowane, function (array $a, array $b): int {
        if ($a['koniec'] === $b['koniec']) {
            if ($a['start'] === $b['start']) {
                return $a['id'] <=> $b['id'];
            }
            return $a['start'] <=> $b['start'];
        }
        return $a['koniec'] <=> $b['koniec'];
    });

    $wybrane = [];
    $ostatniKoniec = -1;

    foreach ($posortowane as $zadanie) {
        if ($zadanie['start'] >= $ostatniKoniec) {
            $wybrane[] = $zadanie;
            $ostatniKoniec = $zadanie['koniec'];
        }
    }

    return $wybrane;
}

function policzKonflikty(array $zadania): array
{
    $wyniki = [];

    foreach ($zadania as $zadanie) {
        $licznik = 0;

        foreach ($zadania as $inne) {
            if ($zadanie['id'] === $inne['id']) {
                continue;
            }

            if (koliduja($zadanie, $inne)) {
                $licznik++;
            }
        }

        $wyniki[] = [
            'zadanie' => $zadanie,
            'kolizje' => $licznik,
        ];
    }

    usort($wyniki, function (array $a, array $b): int {
        if ($a['kolizje'] === $b['kolizje']) {
            return $a['zadanie']['id'] <=> $b['zadanie']['id'];
        }
        return $b['kolizje'] <=> $a['kolizje'];
    });

    return $wyniki;
}

function przydzielSale(array $zadania): array
{
    $posortowane = $zadania;

    usort($posortowane, function (array $a, array $b): int {
        if ($a['start'] === $b['start']) {
            return $a['id'] <=> $b['id'];
        }
        return $a['start'] <=> $b['start'];
    });

    $sale = [];

    foreach ($posortowane as $zadanie) {
        $przydzielono = false;

        foreach ($sale as $i => $sala) {
            $ostatnie = $sala[count($sala) - 1];

            if ($ostatnie['koniec'] <= $zadanie['start']) {
                $sale[$i][] = $zadanie;
                $przydzielono = true;
                break;
            }
        }

        if (!$przydzielono) {
            $sale[] = [$zadanie];
        }
    }

    return $sale;
}

$wybrane = greedyJednaSala($zadania);
$konflikty = policzKonflikty($zadania);
$sale = przydzielSale($zadania);

echo "Algorytm zachłanny (jedna sala):" . PHP_EOL;
echo "  Wybrane zadania (" . count($wybrane) . "): " . implode(', ', array_map(fn(array $z): string => $z['nazwa'], $wybrane)) . PHP_EOL;
echo "  Kolejność decyzji: " . implode(' -> ', array_map(fn(array $z): string => formatujZadanie($z), $wybrane)) . PHP_EOL;
echo PHP_EOL;

echo "Konflikty:" . PHP_EOL;
echo "  Najbardziej konfliktowe: " . $konflikty[0]['zadanie']['nazwa'] . " (" . $konflikty[0]['kolizje'] . " kolizji z innymi zadaniami)" . PHP_EOL;
echo PHP_EOL;

echo "Minimalna liczba sal: " . count($sale) . PHP_EOL;

foreach ($sale as $i => $sala) {
    echo "  Sala " . ($i + 1) . ": " . implode(', ', array_map(fn(array $z): string => formatujZadanie($z), $sala)) . PHP_EOL;
}
