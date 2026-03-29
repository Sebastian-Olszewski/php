<?php

declare(strict_types=1);

function mergeSort(array $arr, int &$comparisons): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);

    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    $left = mergeSort($left, $comparisons);
    $right = mergeSort($right, $comparisons);

    return merge($left, $right, $comparisons);
}

function merge(array $left, array $right, int &$comparisons): array
{
    $result = [];
    $i = 0;
    $j = 0;

    $leftCount = count($left);
    $rightCount = count($right);

    while ($i < $leftCount && $j < $rightCount) {
        $comparisons++;

        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }

    while ($i < $leftCount) {
        $result[] = $left[$i];
        $i++;
    }

    while ($j < $rightCount) {
        $result[] = $right[$j];
        $j++;
    }

    return $result;
}

function formatArray(array $arr, bool $short = false): string
{
    if ($short && count($arr) > 10) {
        return '[' . $arr[0] . ', ' . $arr[1] . ', ' . $arr[2] . ', ..., ' . $arr[count($arr) - 1] . ']';
    }

    return '[' . implode(', ', $arr) . ']';
}

$tablice = [
        [5, 3, 8, 1, 9, 2],
        [38, 27, 43, 3, 9, 82, 10, 15],
        [64, 25, 12, 22, 11, 90, 3, 47, 71, 38, 55, 8],
        [25, 24, 23, 22, 21, 20, 19, 18, 17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
];

$wynikiMergeSort = [];

foreach ($tablice as $index => $tablica) {
    $comparisons = 0;
    $sorted = mergeSort($tablica, $comparisons);
    $wynikiMergeSort[] = $sorted;

    $n = count($tablica);
    $k = $comparisons / ($n * log($n, 2));

    echo "n={$n}";

    if ($n === 25) {
        echo " | (malejący 25..1)";
    }

    echo PHP_EOL;
    echo " | Wejście: " . formatArray($tablica) . PHP_EOL;
    echo " | Wyjście: " . formatArray($sorted, $n === 25) . PHP_EOL;
    echo " | Porównania: {$comparisons} | K: " . number_format($k, 3, '.', '') . PHP_EOL;
    echo PHP_EOL;
}

$doWeryfikacji = $tablice[0];
sort($doWeryfikacji);

$status = ($doWeryfikacji === $wynikiMergeSort[0]) ? 'ZGODNA' : 'NIEZGODNA';

echo "Weryfikacja z sort(): {$status}" . PHP_EOL;
