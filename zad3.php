<?php

declare(strict_types=1);

$dokumenty = [
    0 => "PHP jest językiem skryptowym używanym do tworzenia stron internetowych",
    1 => "Tablice w PHP mogą być indeksowane lub asocjacyjne i bardzo przydatne",
    2 => "Funkcje array_map i array_filter ułatwiają przetwarzanie tablic w PHP",
    3 => "PHP obsługuje tablice wielowymiarowe i zagnieżdżone struktury danych",
    4 => "Serwer Apache współpracuje z PHP do obsługi żądań HTTP i połączeń",
    5 => "Bazy danych MySQL są często używane razem z PHP do przechowywania",
    6 => "Funkcja usort sortuje tablice w PHP według różnych kryteriów i warunków",
    7 => "JavaScript i PHP razem tworzą dynamiczne aplikacje internetowe i serwisy",
    8 => "PHP posiada wbudowane funkcje do pracy z plikami tablicami i bazami",
    9 => "Bezpieczeństwo aplikacji PHP wymaga walidacji danych wejściowych i filtrów",
];

$stopWords = ['i', 'w', 'na', 'do', 'z', 'są', 'lub', 'być', 'może', 'jest', 'się'];

function normalizeText(string $text): array
{
    global $stopWords;

    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\s]+/u', ' ', $text);
    $words = preg_split('/\s+/u', trim($text));

    $result = [];

    foreach ($words as $word) {
        if ($word === '') {
            continue;
        }

        if (mb_strlen($word, 'UTF-8') < 3) {
            continue;
        }

        if (in_array($word, $stopWords, true)) {
            continue;
        }

        $result[] = $word;
    }

    return $result;
}

function buildIndex(array $documents): array
{
    $index = [];
    $globalFreq = [];

    foreach ($documents as $docId => $text) {
        $words = normalizeText($text);

        foreach ($words as $word) {
            if (!isset($index[$word])) {
                $index[$word] = [];
            }

            if (!isset($index[$word][$docId])) {
                $index[$word][$docId] = 0;
            }

            $index[$word][$docId]++;

            if (!isset($globalFreq[$word])) {
                $globalFreq[$word] = 0;
            }

            $globalFreq[$word]++;
        }
    }

    arsort($globalFreq);

    return [$index, $globalFreq];
}

function normalizeQuery(array $query): array
{
    $result = [];

    foreach ($query as $word) {
        $parts = normalizeText($word);
        foreach ($parts as $part) {
            $result[] = $part;
        }
    }

    return array_values(array_unique($result));
}

function searchAnd(array $query, array $index): array
{
    $query = normalizeQuery($query);

    if ($query === []) {
        return [];
    }

    $docLists = [];

    foreach ($query as $word) {
        if (!isset($index[$word])) {
            return [];
        }

        $docLists[] = array_keys($index[$word]);
    }

    $commonDocs = array_shift($docLists);

    foreach ($docLists as $docs) {
        $commonDocs = array_values(array_intersect($commonDocs, $docs));
    }

    $results = [];

    foreach ($commonDocs as $docId) {
        $score = 0;
        $details = [];

        foreach ($query as $word) {
            $tf = $index[$word][$docId] ?? 0;
            $score += $tf;
            $details[$word] = $tf;
        }

        $results[] = [
            'doc_id' => $docId,
            'score' => $score,
            'details' => $details,
        ];
    }

    usort($results, function (array $a, array $b): int {
        if ($b['score'] === $a['score']) {
            return $a['doc_id'] <=> $b['doc_id'];
        }
        return $b['score'] <=> $a['score'];
    });

    return $results;
}

function searchOr(array $query, array $index): array
{
    $query = normalizeQuery($query);

    if ($query === []) {
        return [];
    }

    $allDocs = [];

    foreach ($query as $word) {
        if (isset($index[$word])) {
            $allDocs = array_merge($allDocs, array_keys($index[$word]));
        }
    }

    $allDocs = array_values(array_unique($allDocs));

    $results = [];

    foreach ($allDocs as $docId) {
        $score = 0;
        $details = [];

        foreach ($query as $word) {
            $tf = $index[$word][$docId] ?? 0;
            if ($tf > 0) {
                $details[$word] = $tf;
                $score += $tf;
            }
        }

        $results[] = [
            'doc_id' => $docId,
            'score' => $score,
            'details' => $details,
        ];
    }

    usort($results, function (array $a, array $b): int {
        if ($b['score'] === $a['score']) {
            return $a['doc_id'] <=> $b['doc_id'];
        }
        return $b['score'] <=> $a['score'];
    });

    return $results;
}

function printTopWords(array $globalFreq, int $limit = 5): void
{
    echo "Top {$limit} najczęstszych słów:" . PHP_EOL;

    $i = 0;
    foreach ($globalFreq as $word => $count) {
        echo "- '{$word}': {$count}x" . PHP_EOL;
        $i++;

        if ($i >= $limit) {
            break;
        }
    }

    echo PHP_EOL;
}

function printResults(string $title, array $results): void
{
    echo $title . PHP_EOL;

    if ($results === []) {
        echo "Brak wyników." . PHP_EOL . PHP_EOL;
        return;
    }

    foreach ($results as $i => $result) {
        $parts = [];
        foreach ($result['details'] as $word => $tf) {
            $parts[] = "{$word}:{$tf}";
        }

        echo ($i + 1) . ". Dokument ID:" . $result['doc_id']
            . " | Score:" . $result['score']
            . " (" . implode(', ', $parts) . ")" . PHP_EOL;
    }

    echo PHP_EOL;
}

[$index, $globalFreq] = buildIndex($dokumenty);

printTopWords($globalFreq, 5);

$andResults = searchAnd(["php", "tablice"], $index);
$orResults = searchOr(["mysql", "javascript"], $index);

printResults("Wyniki dla (php AND tablice):", $andResults);
printResults("Wyniki dla (mysql OR javascript):", $orResults);
