<?php

declare(strict_types=1);

function s_push(array &$stos, $val): void
{
    array_splice($stos, count($stos), 0, [$val]);
}

function s_pop(array &$stos)
{
    if (count($stos) === 0) {
        return null;
    }

    $top = $stos[count($stos) - 1];
    array_splice($stos, -1, 1);

    return $top;
}

function s_peek(array $stos)
{
    if (count($stos) === 0) {
        return null;
    }

    return $stos[count($stos) - 1];
}

function validateBrackets(string $text): bool
{
    $stos = [];
    $pairs = [
        ')' => '(',
        ']' => '[',
        '}' => '{',
    ];

    $open = ['(', '[', '{'];

    for ($i = 0; $i < strlen($text); $i++) {
        $ch = $text[$i];

        if (in_array($ch, $open, true)) {
            s_push($stos, $ch);
        } elseif (isset($pairs[$ch])) {
            if (count($stos) === 0) {
                return false;
            }

            $top = s_pop($stos);

            if ($top !== $pairs[$ch]) {
                return false;
            }
        }
    }

    return count($stos) === 0;
}

function evaluateRpn(string $expression): float
{
    $stos = [];
    $tokens = explode(' ', trim($expression));

    foreach ($tokens as $token) {
        if ($token === '') {
            continue;
        }

        if (is_numeric($token)) {
            s_push($stos, (float)$token);
            continue;
        }

        $b = s_pop($stos);
        $a = s_pop($stos);

        switch ($token) {
            case '+':
                $result = $a + $b;
                break;
            case '-':
                $result = $a - $b;
                break;
            case '*':
                $result = $a * $b;
                break;
            case '/':
                $result = $a / $b;
                break;
            default:
                throw new RuntimeException("Nieznany operator: {$token}");
        }

        s_push($stos, $result);
    }

    return (float) s_pop($stos);
}

function formatNumber(float $value): string
{
    if (abs($value - round($value)) < 0.0000000001) {
        return (string) (int) round($value);
    }

    return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
}

function getCircularBufferContents(array $buffer, int $pos, int $size): array
{
    if ($pos < $size) {
        return array_slice($buffer, 0, $pos);
    }

    $start = $pos % $size;

    return array_merge(
        array_slice($buffer, $start),
        array_slice($buffer, 0, $start)
    );
}

$wyrazenia_ONP = [
    "5 2 + 3 *",
    "15 7 1 1 + - / 3 * 2 1 1 + + -",
    "4 13 5 / +",
    "2 3 + 4 * 5 -",
    "100 50 25 / -",
];

$napisy_nawiasy = [
    "[({()})]",
    "((())",
    "{[()]}",
    "([)]",
    "",
];

$bufferSize = 5;
$buffer = array_fill(0, $bufferSize, null);
$pos = 0;

foreach ($wyrazenia_ONP as $i => $wyrazenie) {
    $nawiasy = $napisy_nawiasy[$i];
    $status = validateBrackets($nawiasy) ? 'OK' : 'BŁĄD';
    $wynik = evaluateRpn($wyrazenie);

    $buffer[$pos % $bufferSize] = $wynik;
    $pos++;

    $left = '[' . ($i + 1) . '] Nawiasy "' . $nawiasy . '": ' . $status;
    $middle = ' | ONP "' . $wyrazenie . '"';

    echo str_pad($left, 32)
        . str_pad($middle, 42)
        . '= ' . formatNumber($wynik) . PHP_EOL;
}

$finalBuffer = getCircularBufferContents($buffer, $pos, $bufferSize);
$finalBuffer = array_map(fn($v) => formatNumber((float)$v), $finalBuffer);

echo PHP_EOL;
echo 'Bufor cykliczny (ostatnie 5 wyników): [' . implode(', ', $finalBuffer) . ']' . PHP_EOL;
