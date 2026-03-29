<?php

declare(strict_types=1);

$dane = [];
$historia = [];

function wczytajLinie(string $prompt): string|false
{
    if (function_exists('readline')) {
        $linia = readline($prompt);
        if ($linia !== false && trim($linia) !== '') {
            readline_add_history($linia);
        }
        return $linia;
    }

    echo $prompt;
    $linia = fgets(STDIN);
    if ($linia === false) {
        return false;
    }

    return rtrim($linia, "\r\n");
}

function formatujWartosc($v): string
{
    if (is_int($v)) {
        return (string)$v;
    }

    if (is_float($v)) {
        if (abs($v - round($v)) < 0.0000000001) {
            return (string)(int)round($v);
        }

        return rtrim(rtrim(number_format($v, 10, '.', ''), '0'), '.');
    }

    return (string)$v;
}

function formatujTablice(array $dane): string
{
    $out = [];

    foreach ($dane as $v) {
        $out[] = formatujWartosc($v);
    }

    return '[' . implode(', ', $out) . ']';
}

function dodajDoHistorii(array &$historia, string $komenda): void
{
    $historia[] = $komenda;
    $historia = array_slice($historia, -10);
}

function parsujLiczbe(string $v): int|float|null
{
    if (!is_numeric($v)) {
        return null;
    }

    $n = $v + 0;

    if (is_int($n)) {
        return $n;
    }

    if (is_float($n) && abs($n - round($n)) < 0.0000000001) {
        return (int)round($n);
    }

    return (float)$n;
}

function pokazPomoc(): void
{
    echo "Dostępne polecenia:" . PHP_EOL;
    echo "  push <v>" . PHP_EOL;
    echo "  pop" . PHP_EOL;
    echo "  insert <idx> <v>" . PHP_EOL;
    echo "  delete <idx>" . PHP_EOL;
    echo "  sort" . PHP_EOL;
    echo "  rsort" . PHP_EOL;
    echo "  filter <op> <v>" . PHP_EOL;
    echo "  unique" . PHP_EOL;
    echo "  reverse" . PHP_EOL;
    echo "  chunk <n>" . PHP_EOL;
    echo "  slice <od> <ile>" . PHP_EOL;
    echo "  stats" . PHP_EOL;
    echo "  show" . PHP_EOL;
    echo "  reset" . PHP_EOL;
    echo "  save" . PHP_EOL;
    echo "  history" . PHP_EOL;
    echo "  count" . PHP_EOL;
    echo "  first" . PHP_EOL;
    echo "  last" . PHP_EOL;
    echo "  sum" . PHP_EOL;
    echo "  avg" . PHP_EOL;
    echo "  help" . PHP_EOL;
    echo "  exit" . PHP_EOL;
}

while (true) {
    $linia = wczytajLinie(">> ");

    if ($linia === false) {
        echo "Do widzenia!" . PHP_EOL;
        break;
    }

    $linia = trim($linia);

    if ($linia === '') {
        continue;
    }

    $czesci = explode(' ', $linia, 3);
    $polecenie = strtolower($czesci[0]);
    $sukces = false;

    switch ($polecenie) {
        case 'push':
            if (!isset($czesci[1])) {
                echo "Brak argumentu dla: push" . PHP_EOL;
                break;
            }

            $wartosc = parsujLiczbe($czesci[1]);

            if ($wartosc === null) {
                echo "Nieprawidłowa wartość: {$czesci[1]}" . PHP_EOL;
                break;
            }

            $dane[] = $wartosc;
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'pop':
            if (count($dane) === 0) {
                echo "Tablica jest pusta" . PHP_EOL;
                break;
            }

            $usuniety = array_pop($dane);
            echo "Usunięto: " . formatujWartosc($usuniety) . PHP_EOL;
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'insert':
            if (!isset($czesci[1]) || !isset($czesci[2])) {
                echo "Brak argumentu dla: insert" . PHP_EOL;
                break;
            }

            $idx = parsujLiczbe($czesci[1]);
            $wartosc = parsujLiczbe($czesci[2]);

            if ($idx === null || !is_int($idx)) {
                echo "Nieprawidłowy indeks: {$czesci[1]}" . PHP_EOL;
                break;
            }

            if ($wartosc === null) {
                echo "Nieprawidłowa wartość: {$czesci[2]}" . PHP_EOL;
                break;
            }

            if ($idx < 0 || $idx > count($dane)) {
                echo "Indeks poza zakresem: {$idx}" . PHP_EOL;
                break;
            }

            array_splice($dane, $idx, 0, [$wartosc]);
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'delete':
            if (!isset($czesci[1])) {
                echo "Brak argumentu dla: delete" . PHP_EOL;
                break;
            }

            $idx = parsujLiczbe($czesci[1]);

            if ($idx === null || !is_int($idx)) {
                echo "Nieprawidłowy indeks: {$czesci[1]}" . PHP_EOL;
                break;
            }

            if ($idx < 0 || $idx >= count($dane)) {
                echo "Indeks poza zakresem: {$idx}" . PHP_EOL;
                break;
            }

            $usuniety = $dane[$idx];
            array_splice($dane, $idx, 1);
            echo "Usunięto: " . formatujWartosc($usuniety) . PHP_EOL;
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'sort':
            sort($dane);
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'rsort':
            rsort($dane);
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'filter':
            $czesciFilter = preg_split('/\s+/', $linia);

            if (count($czesciFilter) < 3) {
                echo "Brak argumentu dla: filter" . PHP_EOL;
                break;
            }

            $op = $czesciFilter[1];
            $wartosc = parsujLiczbe($czesciFilter[2]);

            if ($wartosc === null) {
                echo "Nieprawidłowa wartość: {$czesciFilter[2]}" . PHP_EOL;
                break;
            }

            $dozwolone = ['>', '<', '>=', '<=', '==', '!='];

            if (!in_array($op, $dozwolone, true)) {
                echo "Nieznany operator: {$op}" . PHP_EOL;
                break;
            }

            $dane = array_values(array_filter($dane, function ($x) use ($op, $wartosc): bool {
                return match ($op) {
                    '>' => $x > $wartosc,
                    '<' => $x < $wartosc,
                    '>=' => $x >= $wartosc,
                    '<=' => $x <= $wartosc,
                    '==' => $x == $wartosc,
                    '!=' => $x != $wartosc,
                };
            }));

            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'unique':
            $dane = array_values(array_unique($dane, SORT_REGULAR));
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'reverse':
            $dane = array_reverse($dane);
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'chunk':
            if (!isset($czesci[1])) {
                echo "Brak argumentu dla: chunk" . PHP_EOL;
                break;
            }

            $n = parsujLiczbe($czesci[1]);

            if ($n === null || !is_int($n) || $n <= 0) {
                echo "Nieprawidłowy rozmiar chunk: {$czesci[1]}" . PHP_EOL;
                break;
            }

            $chunks = array_chunk($dane, $n);

            if ($chunks === []) {
                echo "Brak danych" . PHP_EOL;
            } else {
                foreach ($chunks as $i => $chunk) {
                    echo "Chunk " . ($i + 1) . ": " . formatujTablice($chunk) . PHP_EOL;
                }
            }

            $sukces = true;
            break;

        case 'slice':
            $czesciSlice = preg_split('/\s+/', $linia);

            if (count($czesciSlice) < 3) {
                echo "Brak argumentu dla: slice" . PHP_EOL;
                break;
            }

            $od = parsujLiczbe($czesciSlice[1]);
            $ile = parsujLiczbe($czesciSlice[2]);

            if ($od === null || !is_int($od)) {
                echo "Nieprawidłowy offset: {$czesciSlice[1]}" . PHP_EOL;
                break;
            }

            if ($ile === null || !is_int($ile)) {
                echo "Nieprawidłowa długość: {$czesciSlice[2]}" . PHP_EOL;
                break;
            }

            echo formatujTablice(array_slice($dane, $od, $ile)) . PHP_EOL;
            $sukces = true;
            break;

        case 'stats':
            if (count($dane) === 0) {
                echo "Tablica jest pusta" . PHP_EOL;
                break;
            }

            $suma = 0;
            $min = $dane[0];
            $max = $dane[0];

            foreach ($dane as $v) {
                $suma += $v;

                if ($v < $min) {
                    $min = $v;
                }

                if ($v > $max) {
                    $max = $v;
                }
            }

            $srednia = $suma / count($dane);

            echo "Suma: " . formatujWartosc($suma)
                . " | Średnia: " . formatujWartosc($srednia)
                . " | Min: " . formatujWartosc($min)
                . " | Max: " . formatujWartosc($max) . PHP_EOL;
            $sukces = true;
            break;

        case 'show':
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'reset':
            $dane = [];
            echo formatujTablice($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'save':
            echo json_encode(['dane' => $dane], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            $sukces = true;
            break;

        case 'history':
            if ($historia === []) {
                echo "Historia jest pusta" . PHP_EOL;
            } else {
                foreach ($historia as $i => $wpis) {
                    echo ($i + 1) . ": " . $wpis . PHP_EOL;
                }
            }
            $sukces = true;
            break;

        case 'count':
            echo count($dane) . PHP_EOL;
            $sukces = true;
            break;

        case 'first':
            if (count($dane) === 0) {
                echo "Tablica jest pusta" . PHP_EOL;
                break;
            }

            echo formatujWartosc($dane[0]) . PHP_EOL;
            $sukces = true;
            break;

        case 'last':
            if (count($dane) === 0) {
                echo "Tablica jest pusta" . PHP_EOL;
                break;
            }

            echo formatujWartosc($dane[count($dane) - 1]) . PHP_EOL;
            $sukces = true;
            break;

        case 'sum':
            $suma = 0;

            foreach ($dane as $v) {
                $suma += $v;
            }

            echo formatujWartosc($suma) . PHP_EOL;
            $sukces = true;
            break;

        case 'avg':
            if (count($dane) === 0) {
                echo "Tablica jest pusta" . PHP_EOL;
                break;
            }

            $suma = 0;

            foreach ($dane as $v) {
                $suma += $v;
            }

            echo formatujWartosc($suma / count($dane)) . PHP_EOL;
            $sukces = true;
            break;

        case 'help':
            pokazPomoc();
            $sukces = true;
            break;

        case 'exit':
            echo "Do widzenia!" . PHP_EOL;
            break 2;

        default:
            echo "Nieznane polecenie: {$polecenie}" . PHP_EOL;
            break;
    }

    if ($sukces) {
        dodajDoHistorii($historia, $linia);
    }
}
