<?php

declare(strict_types=1);

if ($argc !== 4) {
    fwrite(STDERR, "Usage: php scripts/build-scripture-library.php EN_USFM_DIR CEB_USFM_DIR OUTPUT\n");
    exit(1);
}

[$script, $englishDir, $cebuanoDir, $output] = $argv;

$selections = [
    ['PSA', 23, 1, 4, 'fear', ['fear', 'afraid', 'danger', 'alone', 'dark', 'kahadlok', 'nahadlok', 'ngitngit', 'nag-inusara']],
    ['PSA', 27, 1, 5, 'courage', ['courage', 'brave', 'enemy', 'attack', 'confidence', 'kaisog', 'kaaway', 'pagsalig']],
    ['PSA', 34, 18, 19, 'grief', ['broken heart', 'heartbroken', 'grief', 'grieving', 'died', 'death', 'loss', 'sad', 'sorrow', 'kasubo', 'guol', 'namatay', 'kamatayon', 'kawad-on', 'masulob-on', 'dugmok']],
    ['PSA', 46, 2, 4, 'crisis', ['trouble', 'crisis', 'disaster', 'unsafe', 'gubot', 'kalisod', 'katalagman']],
    ['PSA', 55, 23, 23, 'burden', ['burden', 'pressure', 'worry', 'overwhelmed', 'bug-at', 'kabalaka', 'nabug-atan']],
    ['PSA', 139, 7, 12, 'presence', ['known', 'unseen', 'invisible', 'far from god', 'presence', 'gihibaloan', 'layo sa dios', 'presensya']],
    ['PSA', 147, 3, 5, 'healing', ['hurt', 'healing', 'wounded', 'pain', 'broken heart', 'kasakit', 'samdan', 'kaayohan']],
    ['PRO', 3, 5, 6, 'guidance', ['decision', 'direction', 'guidance', 'confused', 'choice', 'choose', 'career', 'desisyon', 'giya', 'libog', 'pili', 'trabaho']],
    ['PRO', 15, 3, 3, 'work', ['work', 'career', 'project', 'exam', 'study', 'trabaho', 'eskwela', 'proyekto', 'pasulit']],
    ['PRO', 16, 17, 17, 'friendship', ['friend', 'friendship', 'lonely', 'support', 'higala', 'panaghigalaay', 'nag-inusara']],
    ['ISA', 40, 28, 31, 'exhaustion', ['tired', 'exhausted', 'weak', 'burnout', 'waiting', 'kapoy', 'gikapoy', 'luya', 'paghulat']],
    ['ISA', 41, 10, 10, 'fear', ['fear', 'afraid', 'weak', 'help me', 'nahadlok', 'kahadlok', 'tabangi ko', 'luya']],
    ['LAM', 3, 21, 26, 'hope', ['hopeless', 'despair', 'waiting', 'mercy', 'hope', 'walay paglaom', 'kaluoy', 'paglaom', 'paghulat']],
    ['MIC', 6, 8, 8, 'purpose', ['purpose', 'justice', 'what should i do', 'calling', 'katuyoan', 'hustisya', 'unsa akong buhaton']],
    ['MAT', 5, 3, 12, 'blessing', ['rejected', 'persecuted', 'mourning', 'poor', 'meek', 'gisalikway', 'gilutos', 'nagbangutan', 'mapainubsanon']],
    ['MAT', 6, 25, 34, 'anxiety', ['anxiety', 'anxious', 'worry', 'future', 'money', 'food', 'kabalaka', 'nabalaka', 'ugma', 'kwarta']],
    ['MAT', 11, 28, 30, 'rest', ['stress', 'stressed', 'tired', 'burden', 'overwhelmed', 'kapoy', 'gikapoy', 'bug-at', 'nabug-atan']],
    ['MRK', 4, 35, 40, 'storm', ['panic', 'storm', 'chaos', 'out of control', 'hadlok', 'unos', 'gubot', 'dili makontrol']],
    ['LUK', 15, 11, 24, 'return', ['lost', 'return to god', 'ashamed', 'unworthy', 'nawala', 'mobalik sa dios', 'ulaw', 'dili takos']],
    ['JHN', 8, 1, 11, 'mercy', ['guilt', 'judged', 'condemned', 'mistake', 'sin', 'sala', 'guilty', 'gihukman', 'sayop']],
    ['JHN', 10, 10, 14, 'care', ['protection', 'safe', 'care', 'life', 'guard', 'panalipod', 'luwas', 'pag-atiman', 'kinabuhi']],
    ['JHN', 14, 1, 6, 'trust', ['troubled', 'uncertain', 'way forward', 'trust', 'death', 'samok', 'walay kasigurohan', 'dalan', 'pagsalig', 'kamatayon']],
    ['JHN', 14, 27, 27, 'peace', ['peace', 'anxiety', 'afraid', 'disturbed', 'kalinaw', 'kabalaka', 'nahadlok', 'samok']],
    ['JHN', 15, 9, 12, 'love', ['unloved', 'love', 'belonging', 'relationship', 'wala gihigugma', 'gugma', 'relasyon']],
    ['JHN', 16, 33, 33, 'courage', ['trouble', 'suffering', 'courage', 'overcome', 'kalisod', 'pag-antos', 'kaisog', 'madaugon']],
    ['ROM', 5, 1, 5, 'suffering', ['suffering', 'endurance', 'hope', 'hardship', 'pag-antos', 'paglahutay', 'paglaom', 'kalisod']],
    ['ROM', 8, 26, 28, 'prayer', ['cannot pray', 'no words', 'weak', 'confused', 'dili makaampo', 'walay masulti', 'luya', 'libog']],
    ['ROM', 8, 31, 39, 'security', ['abandoned', 'separated', 'unloved', 'against me', 'gibiyaan', 'nahimulag', 'wala gihigugma', 'batok kanako']],
    ['ROM', 12, 17, 21, 'conflict', ['revenge', 'enemy', 'conflict', 'hurt by someone', 'balos', 'kaaway', 'panagbangi', 'gipasakitan']],
    ['1CO', 10, 13, 13, 'temptation', ['temptation', 'addiction', 'habit', 'self control', 'tintasyon', 'bisyo', 'naandan', 'pagpugong sa kaugalingon']],
    ['1CO', 13, 4, 8, 'love', ['marriage', 'relationship', 'love', 'impatient', 'jealous', 'kaminyoon', 'relasyon', 'gugma', 'selos']],
    ['2CO', 1, 3, 5, 'comfort', ['grief', 'comfort', 'loss', 'mourning', 'kasubo', 'paghupay', 'kawad-on', 'pagbangutan']],
    ['2CO', 4, 7, 9, 'resilience', ['pressure', 'failure', 'defeated', 'struggle', 'presyur', 'kapakyasan', 'napildi', 'pakigbisog']],
    ['2CO', 12, 9, 10, 'weakness', ['weakness', 'not enough', 'failure', 'disability', 'kahuyang', 'kulang', 'kapakyasan']],
    ['GAL', 5, 22, 23, 'character', ['anger', 'patience', 'self control', 'character', 'kasuko', 'pailob', 'pagpugong', 'kinaiya']],
    ['EPH', 4, 26, 32, 'forgiveness', ['angry', 'anger', 'forgive', 'resentment', 'kasuko', 'pasaylo', 'dumot']],
    ['PHP', 4, 4, 9, 'anxiety', ['anxiety', 'stress', 'worry', 'peace', 'nabalaka', 'stress', 'kabalaka', 'kalinaw']],
    ['PHP', 4, 11, 13, 'contentment', ['money', 'contentment', 'not enough', 'need', 'kwarta', 'pagkatagbaw', 'kulang', 'panginahanglan']],
    ['COL', 3, 12, 17, 'relationships', ['family', 'relationship', 'forgiveness', 'patience', 'pamilya', 'relasyon', 'pasaylo', 'pailob']],
    ['1TH', 5, 16, 18, 'gratitude', ['grateful', 'thankful', 'joy', 'prayer', 'mapasalamaton', 'kalipay', 'pag-ampo']],
    ['HEB', 4, 14, 16, 'mercy', ['guilt', 'mercy', 'help', 'temptation', 'sala', 'kaluoy', 'tabang', 'tintasyon']],
    ['HEB', 12, 1, 3, 'endurance', ['giving up', 'quit', 'endurance', 'tired', 'mohunong', 'mosurender', 'paglahutay', 'kapoy']],
    ['JAS', 1, 2, 8, 'wisdom', ['trial', 'wisdom', 'doubt', 'decision', 'pagsulay', 'kaalam', 'pagduhaduha', 'desisyon']],
    ['JAS', 1, 19, 20, 'anger', ['anger', 'argument', 'listen', 'temper', 'kasuko', 'lalis', 'pamati', 'sapot']],
    ['1PE', 5, 6, 10, 'anxiety', ['anxiety', 'worry', 'suffering', 'attack', 'kabalaka', 'pag-antos', 'pagsulay']],
    ['1JN', 1, 9, 9, 'confession', ['guilt', 'confess', 'sin', 'forgiveness', 'sala', 'isugid', 'pasaylo']],
    ['1JN', 4, 7, 12, 'love', ['love god', 'love others', 'hate', 'community', 'gugma sa dios', 'gugma sa isigkatawo', 'dumot', 'komunidad']],
    ['1JN', 4, 18, 19, 'fear', ['fear', 'punishment', 'unloved', 'kahadlok', 'silot', 'wala gihigugma']],
    ['REV', 21, 1, 5, 'hope', ['death', 'grief', 'tears', 'new beginning', 'kamatayon', 'kasubo', 'luha', 'bag-ong sinugdanan']],
];

$translations = [
    'en' => ['dir' => $englishDir, 'name' => 'World English Bible Updated · Public domain', 'prefix' => 'webu'],
    'ceb' => ['dir' => $cebuanoDir, 'name' => 'Biblica® Open Ang Pulong sa Dios™ 2024 · CC BY-SA 4.0', 'prefix' => 'ceb'],
];

$result = [];
foreach ($translations as $language => $translation) {
    $books = loadBooks($translation['dir']);
    foreach ($selections as [$book, $chapter, $from, $to, $topic, $keywords]) {
        $sourceChapter = is_array($chapter) ? $chapter[$language] : $chapter;
        if (!isset($books[$book]['verses'][$sourceChapter])) {
            fwrite(STDERR, "Missing {$language} {$book} {$sourceChapter}\n");
            exit(1);
        }

        $texts = [];
        for ($verse = $from; $verse <= $to; $verse++) {
            if (!isset($books[$book]['verses'][$sourceChapter][$verse])) {
                fwrite(STDERR, "Missing {$language} {$book} {$sourceChapter}:{$verse}\n");
                exit(1);
            }
            $texts[] = $books[$book]['verses'][$sourceChapter][$verse];
        }

        $range = $from === $to ? (string) $from : "{$from}–{$to}";
        $result[] = [
            'id' => "{$translation['prefix']}-" . strtolower($book) . "-{$sourceChapter}-{$from}-{$to}",
            'language' => $language,
            'topic' => $topic,
            'keywords' => $keywords,
            'reference' => "{$books[$book]['name']} {$sourceChapter}:{$range}",
            'translation' => $translation['name'],
            'text' => implode(' ', $texts),
        ];
    }
}

$export = preg_replace('/[ \t]+$/m', '', var_export($result, true)) ?? var_export($result, true);
$php = "<?php\n\ndeclare(strict_types=1);\n\n// Generated from verified USFM sources by scripts/build-scripture-library.php.\nreturn "
    . $export . ";\n";

if (file_put_contents($output, $php) === false) {
    fwrite(STDERR, "Could not write {$output}\n");
    exit(1);
}

fwrite(STDOUT, 'Wrote ' . count($result) . " reviewed passages to {$output}\n");

function loadBooks(string $directory): array
{
    $books = [];
    foreach (glob(rtrim($directory, '/') . '/*.usfm') ?: [] as $file) {
        $raw = file_get_contents($file);
        if ($raw === false || !preg_match('/^\\\\id\s+([A-Z0-9]{3})/m', $raw, $idMatch)) {
            continue;
        }

        $id = $idMatch[1];
        preg_match('/^\\\\toc2\s+(.+)$/m', $raw, $nameMatch);
        $name = trim($nameMatch[1] ?? $id);
        $chapter = 0;
        $verse = null;
        $verses = [];

        foreach (preg_split('/\\R/u', $raw) ?: [] as $line) {
            if (preg_match('/^\\\\c\s+(\d+)/', $line, $match)) {
                $chapter = (int) $match[1];
                $verse = null;
                continue;
            }
            if (preg_match('/^\\\\v\s+(\d+)(?:-([0-9]+))?\s*(.*)$/u', $line, $match)) {
                $verse = (int) $match[1];
                $endVerse = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : $verse;
                $verses[$chapter][$verse] = cleanUsfm($match[3]);
                for ($combinedVerse = $verse + 1; $combinedVerse <= $endVerse; $combinedVerse++) {
                    $verses[$chapter][$combinedVerse] = '';
                }
                continue;
            }
            if ($verse !== null && !preg_match('/^\\\\(?:c|s\d*|r|d|qa|cl|mt\d*|toc\d|h|id)\b/u', $line)) {
                $continuation = cleanUsfm($line);
                if ($continuation !== '') {
                    $verses[$chapter][$verse] .= ' ' . $continuation;
                }
            }
        }

        $books[$id] = ['name' => $name, 'verses' => $verses];
    }

    return $books;
}

function cleanUsfm(string $text): string
{
    $text = preg_replace('/\\\\f\s.*?\\\\f\*/u', '', $text) ?? $text;
    $text = preg_replace('/\\\\x\s.*?\\\\x\*/u', '', $text) ?? $text;
    $text = preg_replace('/\\\\\+?w\s+([^|]+)\|.*?\\\\\+?w\*/u', '$1', $text) ?? $text;
    $text = preg_replace('/\\\\[a-z0-9+\-]+\*/iu', '', $text) ?? $text;
    $text = preg_replace('/\\\\[a-z0-9+\-]+\s+/iu', ' ', $text) ?? $text;
    $text = preg_replace('/\\\\[a-z0-9+\-]+$/iu', '', $text) ?? $text;
    $text = str_replace(["\u{00A0}", ' // '], [' ', ' '], $text);
    $text = preg_replace('/([’\'])\s+([\p{L}])/u', '$1$2', $text) ?? $text;
    $text = preg_replace('/([“‘])\s+/u', '$1', $text) ?? $text;
    return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
}
