<?php

declare(strict_types=1);

$oceny = [
    "Anna"    => [5, 4, null, 2, null, 3, 4, 5],
    "Bartek"  => [4, 5, 3, null, 2, 4, null, 4],
    "Celina"  => [5, 3, null, 3, null, 4, 5, null],
    "Dawid"   => [2, null, 4, 5, 3, null, 2, 3],
    "Ewa"     => [null, 4, 3, null, 5, 3, 4, 2],
    "Filip"   => [3, 5, 4, 2, null, 5, null, 4],
    "Grażyna" => [5, null, 2, 4, 3, 2, 5, null],
];

$produkty = ["Laptop", "Monitor", "Klawiatura", "Mysz", "Słuchawki", "Kamera", "Tablet", "Głośnik"];

function pearson(array $a, array $b): float
{
    $xa = [];
    $xb = [];

    for ($i = 0; $i < count($a); $i++) {
        if ($a[$i] !== null && $b[$i] !== null) {
            $xa[] = $a[$i];
            $xb[] = $b[$i];
        }
    }

    $n = count($xa);

    if ($n < 2) {
        return 0.0;
    }

    $avgA = array_sum($xa) / $n;
    $avgB = array_sum($xb) / $n;

    $num = 0.0;
    $sumA = 0.0;
    $sumB = 0.0;

    for ($i = 0; $i < $n; $i++) {
        $da = $xa[$i] - $avgA;
        $db = $xb[$i] - $avgB;
        $num += $da * $db;
        $sumA += $da * $da;
        $sumB += $db * $db;
    }

    $den = sqrt($sumA * $sumB);

    if ($den == 0.0) {
        return 0.0;
    }

    return $num / $den;
}

function similaritiesForUser(string $target, array $oceny): array
{
    $wyniki = [];

    foreach ($oceny as $user => $ratings) {
        if ($user === $target) {
            continue;
        }

        $wyniki[] = [
            'user' => $user,
            'sim' => pearson($oceny[$target], $ratings),
        ];
    }

    usort($wyniki, function (array $a, array $b): int {
        if (abs($a['sim'] - $b['sim']) < 0.0000000001) {
            return strcmp($a['user'], $b['user']);
        }
        return $b['sim'] <=> $a['sim'];
    });

    return $wyniki;
}

function kNearestNeighbors(string $target, array $oceny, int $k): array
{
    return array_slice(similaritiesForUser($target, $oceny), 0, $k);
}

function predictRating(string $target, int $productIndex, array $oceny, array $neighbors): ?float
{
    $num = 0.0;
    $den = 0.0;

    foreach ($neighbors as $neighbor) {
        $user = $neighbor['user'];
        $sim = $neighbor['sim'];
        $rating = $oceny[$user][$productIndex];

        if ($rating !== null) {
            $num += $sim * $rating;
            $den += abs($sim);
        }
    }

    if ($den == 0.0) {
        return null;
    }

    return $num / $den;
}

function recommendationsForUser(string $target, array $oceny, array $produkty, int $k): array
{
    $neighbors = kNearestNeighbors($target, $oceny, $k);
    $wyniki = [];

    for ($i = 0; $i < count($produkty); $i++) {
        if ($oceny[$target][$i] === null) {
            $pred = predictRating($target, $i, $oceny, $neighbors);

            if ($pred !== null) {
                $wyniki[] = [
                    'produkt' => $produkty[$i],
                    'ocena' => $pred,
                ];
            }
        }
    }

    usort($wyniki, function (array $a, array $b): int {
        if (abs($a['ocena'] - $b['ocena']) < 0.0000000001) {
            return strcmp($a['produkt'], $b['produkt']);
        }
        return $b['ocena'] <=> $a['ocena'];
    });

    return $wyniki;
}

function popularProducts(array $oceny, array $produkty, array $ratedByUser): array
{
    $wyniki = [];

    for ($i = 0; $i < count($produkty); $i++) {
        if ($ratedByUser[$i] !== null) {
            continue;
        }

        $sum = 0.0;
        $count = 0;

        foreach ($oceny as $ratings) {
            if ($ratings[$i] !== null) {
                $sum += $ratings[$i];
                $count++;
            }
        }

        if ($count > 0) {
            $wyniki[] = [
                'produkt' => $produkty[$i],
                'srednia' => $sum / $count,
            ];
        }
    }

    usort($wyniki, function (array $a, array $b): int {
        if (abs($a['srednia'] - $b['srednia']) < 0.0000000001) {
            return strcmp($a['produkt'], $b['produkt']);
        }
        return $b['srednia'] <=> $a['srednia'];
    });

    return $wyniki;
}

$target = "Anna";
$k = 3;

$similarities = similaritiesForUser($target, $oceny);
$neighbors = kNearestNeighbors($target, $oceny, $k);
$recommendations = recommendationsForUser($target, $oceny, $produkty, $k);

echo "Podobieństwo Pearsona dla Anny:" . PHP_EOL;
echo PHP_EOL;

foreach ($similarities as $row) {
    echo "  " . str_pad($row['user'] . ':', 10) . " " . number_format($row['sim'], 4, '.', '') . PHP_EOL;
}

echo PHP_EOL;
echo "k=3 sąsiedzi Anny: ";

$neighborTexts = [];
foreach ($neighbors as $n) {
    $neighborTexts[] = $n['user'] . '(' . number_format($n['sim'], 4, '.', '') . ')';
}

echo implode(', ', $neighborTexts) . PHP_EOL;
echo PHP_EOL;

echo "Rekomendacje dla Anny (produkty nieocenione):" . PHP_EOL;

foreach ($recommendations as $i => $rec) {
    echo "  " . ($i + 1) . ". " . str_pad($rec['produkt'], 12) . " — przewidywana ocena: " . number_format($rec['ocena'], 2, '.', '') . PHP_EOL;
}

echo PHP_EOL;

$hania = [4, null, null, null, null, null, null, null];
$canUsePearson = false;

foreach ($oceny as $ratings) {
    $common = 0;

    for ($i = 0; $i < count($hania); $i++) {
        if ($hania[$i] !== null && $ratings[$i] !== null) {
            $common++;
        }
    }

    if ($common >= 2) {
        $canUsePearson = true;
        break;
    }
}

echo "Zimny start (Hania, 1 ocena):" . PHP_EOL;

if (!$canUsePearson) {
    echo "  Za mało wspólnych ocen z innymi użytkownikami — brak wiarygodnych korelacji." . PHP_EOL;
    echo "  Strategia: rekomenduj najpopularniejsze produkty (najwyższa średnia ocen wśród wszystkich)." . PHP_EOL;

    $popularne = popularProducts($oceny, $produkty, $hania);
    $top = array_slice($popularne, 0, 3);

    echo "  Przykład: ";

    $popularTexts = [];
    foreach ($top as $p) {
        $popularTexts[] = $p['produkt'] . '(' . number_format($p['srednia'], 2, '.', '') . ')';
    }

    echo implode(', ', $popularTexts) . PHP_EOL;
}
