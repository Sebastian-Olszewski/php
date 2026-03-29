<?php

declare(strict_types=1);

$transakcje = [
    ["id"=>1, "data"=>"2024-01-15", "kategoria"=>"Elektronika", "kwota"=>1200.00],
    ["id"=>2, "data"=>"2024-01-22", "kategoria"=>"Dom", "kwota"=>350.00],
    ["id"=>3, "data"=>"2024-02-03", "kategoria"=>"Elektronika", "kwota"=>800.00],
    ["id"=>4, "data"=>"2024-02-14", "kategoria"=>"Odzież", "kwota"=>250.00],
    ["id"=>5, "data"=>"2024-02-28", "kategoria"=>"Dom", "kwota"=>420.00],
    ["id"=>6, "data"=>"2024-03-05", "kategoria"=>"Elektronika", "kwota"=>1500.00],
    ["id"=>7, "data"=>"2024-03-12", "kategoria"=>"Odzież", "kwota"=>180.00],
    ["id"=>8, "data"=>"2024-03-19", "kategoria"=>"Dom", "kwota"=>290.00],
    ["id"=>9, "data"=>"2024-01-08", "kategoria"=>"Odzież", "kwota"=>310.00],
    ["id"=>10, "data"=>"2024-01-30", "kategoria"=>"Elektronika", "kwota"=>950.00],
    ["id"=>11, "data"=>"2024-02-10", "kategoria"=>"Dom", "kwota"=>600.00],
    ["id"=>12, "data"=>"2024-03-25", "kategoria"=>"Odzież", "kwota"=>430.00],
    ["id"=>13, "data"=>"2024-01-18", "kategoria"=>"Elektronika", "kwota"=>2100.00],
    ["id"=>14, "data"=>"2024-02-22", "kategoria"=>"Dom", "kwota"=>175.00],
    ["id"=>15, "data"=>"2024-03-08", "kategoria"=>"Elektronika", "kwota"=>670.00],
    ["id"=>16, "data"=>"2024-01-25", "kategoria"=>"Odzież", "kwota"=>520.00],
    ["id"=>17, "data"=>"2024-02-17", "kategoria"=>"Elektronika", "kwota"=>1350.00],
    ["id"=>18, "data"=>"2024-03-14", "kategoria"=>"Dom", "kwota"=>480.00],
    ["id"=>19, "data"=>"2024-01-12", "kategoria"=>"Dom", "kwota"=>230.00],
    ["id"=>20, "data"=>"2024-02-05", "kategoria"=>"Odzież", "kwota"=>390.00],
];

function stddev(array $values): float
{
    $n = count($values);
    $sum = 0.0;

    foreach ($values as $value) {
        $sum += $value;
    }

    $avg = $sum / $n;
    $varianceSum = 0.0;

    foreach ($values as $value) {
        $varianceSum += ($value - $avg) * ($value - $avg);
    }

    return sqrt($varianceSum / $n);
}

$miesiace = [
    '2024-01' => 'Styczeń',
    '2024-02' => 'Luty',
    '2024-03' => 'Marzec',
];

$pivot = [];
$kwotyKategorii = [];

foreach ($transakcje as $t) {
    $kategoria = $t['kategoria'];
    $miesiac = substr($t['data'], 0, 7);
    $kwota = $t['kwota'];

    if (!isset($pivot[$kategoria])) {
        $pivot[$kategoria] = [];
    }

    if (!isset($pivot[$kategoria][$miesiac])) {
        $pivot[$kategoria][$miesiac] = 0.0;
    }

    if (!isset($kwotyKategorii[$kategoria])) {
        $kwotyKategorii[$kategoria] = [];
    }

    $pivot[$kategoria][$miesiac] += $kwota;
    $kwotyKategorii[$kategoria][] = $kwota;
}

ksort($pivot);
ksort($kwotyKategorii);

printf("%-14s | %8s | %8s | %8s\n", 'Kategoria', 'Styczeń', 'Luty', 'Marzec');
echo str_repeat('-', 14) . '-|-' . str_repeat('-', 8) . '-|-' . str_repeat('-', 8) . '-|-' . str_repeat('-', 8) . PHP_EOL;

foreach ($pivot as $kategoria => $wiersz) {
    $jan = $wiersz['2024-01'] ?? 0.0;
    $feb = $wiersz['2024-02'] ?? 0.0;
    $mar = $wiersz['2024-03'] ?? 0.0;

    printf("%-14s | %8.2f | %8.2f | %8.2f\n", $kategoria, $jan, $feb, $mar);
}

echo PHP_EOL;
echo "Odchylenia standardowe (σ):" . PHP_EOL;

$maxKategoria = '';
$maxSigma = -1.0;

foreach ($kwotyKategorii as $kategoria => $kwoty) {
    $n = count($kwoty);
    $sum = 0.0;

    foreach ($kwoty as $kwota) {
        $sum += $kwota;
    }

    $avg = $sum / $n;
    $sigma = stddev($kwoty);

    printf("  %-12s : σ=%.2f (n=%d, avg=%.2f zł)\n", $kategoria, $sigma, $n, $avg);

    if ($sigma > $maxSigma) {
        $maxSigma = $sigma;
        $maxKategoria = $kategoria;
    }
}

echo PHP_EOL;
printf("Kategoria o największej zmienności: %s (σ=%.2f)\n", $maxKategoria, $maxSigma);
