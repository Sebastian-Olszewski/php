<?php

declare(strict_types=1);

function sito(int $n): array
{
    $A = array_fill(0, $n + 1, true);

    if ($n >= 0) {
        $A[0] = false;
    }
    if ($n >= 1) {
        $A[1] = false;
    }

    for ($i = 2; $i * $i <= $n; $i++) {
        if ($A[$i]) {
            for ($j = $i * $i; $j <= $n; $j += $i) {
                $A[$j] = false;
            }
        }
    }

    $primes = [];
    for ($i = 2; $i <= $n; $i++) {
        if ($A[$i]) {
            $primes[] = $i;
        }
    }

    return $primes;
}

function primesInRange(array $primes, int $a, int $b): array
{
    return array_values(array_filter($primes, function (int $p) use ($a, $b): bool {
        return $p >= $a && $p <= $b;
    }));
}

function theoreticalDensity(int $a, int $b): float
{
    $length = $b - $a + 1;
    $middle = ($a + $b) / 2;

    return $length / log($middle);
}

function goldbachPairs(int $n, array $primes, array $primeSet): array
{
    $pairs = [];

    foreach ($primes as $p) {
        if ($p > intdiv($n, 2)) {
            break;
        }

        $q = $n - $p;

        if (isset($primeSet[$q])) {
            $pairs[] = [$p, $q];
        }
    }

    return $pairs;
}

$primes = sito(500);
$primeSet = array_flip($primes);

echo "Liczby pierwsze [1-100] (bloki po 10):" . PHP_EOL;
$first100 = primesInRange($primes, 1, 100);
$chunks = array_chunk($first100, 10);

foreach ($chunks as $chunk) {
    echo "[" . implode(", ", $chunk) . "]" . PHP_EOL;
}

echo PHP_EOL;
echo "Gęstość liczb pierwszych:" . PHP_EOL;

$intervals = [
    [1, 100],
    [101, 200],
    [201, 300],
    [301, 400],
    [401, 500],
];

foreach ($intervals as [$a, $b]) {
    $count = count(primesInRange($primes, $a, $b));
    $theoretical = theoreticalDensity($a, $b);

    echo "Przedział [{$a}-{$b}]: {$count} (teoretycznie: ~" .
        number_format($theoretical, 1, '.', '') . ")" . PHP_EOL;
}

echo PHP_EOL;

$maxNumber = 0;
$maxPairsCount = 0;

for ($n = 4; $n <= 200; $n += 2) {
    $pairs = goldbachPairs($n, $primes, $primeSet);
    $count = count($pairs);

    if ($count > $maxPairsCount) {
        $maxPairsCount = $count;
        $maxNumber = $n;
    }
}

$pairsFor30 = goldbachPairs(30, $primes, $primeSet);
$formattedPairs30 = array_map(function (array $pair): string {
    return "[" . $pair[0] . "+" . $pair[1] . "]";
}, $pairsFor30);

echo "Goldbach — najwięcej par w [4, 200]: liczba {$maxNumber} ({$maxPairsCount} par)" . PHP_EOL;
echo "Pary Goldbacha dla 30: " . implode(", ", $formattedPairs30) . PHP_EOL;
