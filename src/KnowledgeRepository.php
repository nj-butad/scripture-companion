<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class KnowledgeRepository
{
    private array $entries;

    public function __construct()
    {
        $this->entries = $this->buildEntries();
    }

    public function isKnowledgeQuery(string $message): bool
    {
        if (preg_match(
            '/\b(rosary|rosaryo|mystery|mysteries|misteryo|decade|bead|hail mary|our father|st\.? michael|guardian angel|angelus|contrition|holy spirit|prayer|pag-ampo|ampo|kristohanon|this app|this site|website|author|creator|founder|testimony|story|southern leyte|virtual assistant|\bva\b|privacy|private|saved|data|openai|ai|translation|cebuano|english bible|how (?:do|can|should) (?:i|we) (?:use|pray)|what (?:is|does) (?:this|the app))\b/iu',
            $message
        ) === 1) {
            return true;
        }

        $normalized = $this->normalize($message);
        foreach ($this->entries as $entry) {
            if (!str_starts_with($entry['id'], 'prayer-')) {
                continue;
            }
            foreach ($entry['title'] as $title) {
                if (str_contains($normalized, $this->normalize($title))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function find(string $message, string $language, int $limit = 5): array
    {
        $normalized = $this->normalize($message);
        $tokens = array_values(array_unique(array_filter(
            preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [],
            static fn (string $token): bool => mb_strlen($token) >= 3
        )));
        $scored = [];

        foreach ($this->entries as $entry) {
            $score = 0;
            foreach ($entry['keywords'] as $keyword) {
                $needle = $this->normalize($keyword);
                if (str_contains($normalized, $needle)) {
                    $score += str_contains($needle, ' ') ? 16 : 9;
                } else {
                    foreach ($tokens as $token) {
                        if (mb_strlen($needle) >= 4 && (str_starts_with($token, $needle) || str_starts_with($needle, $token))) {
                            $score += 3;
                            break;
                        }
                    }
                }
            }

            if (preg_match('/\b(mystery|mysteries|misteryo)\b/iu', $normalized) === 1
                && ($entry['id'] === 'rosary-schedule' || str_starts_with($entry['id'], 'mystery-'))) {
                $score += 60;
            }
            if (preg_match('/\b(today|karon|karong adlawa)\b/iu', $normalized) === 1
                && $entry['id'] === 'rosary-today') {
                $score += 100;
            }
            if (preg_match('/\b(rosary|rosaryo|bead|decade)\b/iu', $normalized) === 1
                && str_starts_with($entry['id'], 'rosary-')) {
                $score += 50;
            }
            if (preg_match('/\b(author|creator|founder|testimony|story|southern leyte|virtual assistant|\bva\b)\b/iu', $normalized) === 1
                && $entry['id'] === 'author-testimony') {
                $score += 70;
            }
            if (preg_match('/\b(privacy|private|saved|store|data|database)\b/iu', $normalized) === 1
                && $entry['id'] === 'privacy-and-ai') {
                $score += 70;
            }
            if (preg_match('/\b(translation|source|license|cebuano bible|english bible)\b/iu', $normalized) === 1
                && $entry['id'] === 'translations') {
                $score += 70;
            }
            if (preg_match('/\b(what can|features|how (?:do|can|should) (?:i|we) use|unsa(?:y| ang) mahimo)\b/iu', $normalized) === 1
                && $entry['id'] === 'site-guide') {
                $score += 80;
            }

            $scored[] = ['score' => $score, 'entry' => $entry];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
        $matches = array_values(array_filter($scored, static fn (array $item): bool => $item['score'] > 0));
        if ($matches === []) {
            $matches = array_values(array_filter(
                $scored,
                static fn (array $item): bool => in_array($item['entry']['id'], ['app-purpose', 'site-guide'], true)
            ));
        }

        return array_map(
            static fn (array $item): array => [
                'id' => $item['entry']['id'],
                'title' => $item['entry']['title'][$language] ?? $item['entry']['title']['en'],
                'content' => $item['entry']['content'][$language] ?? $item['entry']['content']['en'],
                'url' => $item['entry']['url'] ?? null,
            ],
            array_slice($matches, 0, $limit)
        );
    }

    private function buildEntries(): array
    {
        $prayers = require dirname(__DIR__) . '/data/prayers.php';
        $rosary = require dirname(__DIR__) . '/data/rosary.php';

        $entries = [
            [
                'id' => 'rosary-how-to',
                'title' => ['en' => 'How to pray the Rosary', 'ceb' => 'Unsaon pag-ampo sa Rosaryo'],
                'content' => [
                    'en' => 'Begin with the Sign of the Cross and the Apostles’ Creed. On the first large bead pray the Our Father. On the next three beads pray three Hail Marys, then the Glory Be. Announce the first mystery and its Scripture reference. Pray one Our Father, ten Hail Marys while meditating on the mystery, the Glory Be, and O My Jesus. Repeat this pattern for all five mysteries. Conclude with the Hail, Holy Queen, its response, the closing prayer, and the Sign of the Cross. The guided page leads through every prayer and bead.',
                    'ceb' => 'Sugdi sa Timaan sa Krus ug Nagatoo. Sa unang dakong lubid iampo ang Amahan Namo. Sa sunod tulo ka lubid iampo ang tulo ka Maghimaya Ka Maria, dayon ang Himaya sa Amahan. Ipahibalo ang unang misteryo ug ang basahon sa Kasulatan. Iampo ang usa ka Amahan Namo, napulo ka Maghimaya Ka Maria samtang namalandong sa misteryo, Himaya sa Amahan, ug O Hesus Ko. Balika kini sa lima ka misteryo. Taposa sa Maghimaya Ka Rayna, tubag, katapusang pag-ampo, ug Timaan sa Krus. Ang guided page mogiya sa matag pag-ampo ug lubid.',
                ],
                'keywords' => ['rosary', 'rosaryo', 'how to pray', 'unsaon pag-ampo', 'bead', 'decade', 'steps'],
                'url' => '/rosary',
            ],
            [
                'id' => 'rosary-schedule',
                'title' => ['en' => 'Rosary mysteries by day', 'ceb' => 'Mga misteryo sa Rosaryo matag adlaw'],
                'content' => [
                    'en' => 'The traditional weekly pattern used by Kristohanon is: Joyful Mysteries on Monday and Saturday; Sorrowful Mysteries on Tuesday and Friday; Glorious Mysteries on Wednesday and Sunday; Luminous Mysteries on Thursday. The Rosary page selects the day automatically in Asia/Manila, and the visitor may choose another set.',
                    'ceb' => 'Ang tradisyonal nga iskedyul nga gigamit sa Kristohanon mao kini: Mga Misteryo sa Kalipay sa Lunes ug Sabado; Mga Misteryo sa Kasakit sa Martes ug Biyernes; Mga Misteryo sa Himaya sa Miyerkoles ug Domingo; Mga Misteryo sa Kahayag sa Huwebes. Awtomatikong pilion sa Rosary page ang misteryo sumala sa adlaw sa Asia/Manila, apan mahimong mopili og laing set.',
                ],
                'keywords' => ['mysteries', 'mystery today', 'misteryo', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'lunes', 'martes', 'miyerkoles', 'huwebes', 'biyernes', 'sabado', 'domingo'],
                'url' => '/rosary',
            ],
            [
                'id' => 'app-purpose',
                'title' => ['en' => 'What Kristohanon is', 'ceb' => 'Unsa ang Kristohanon'],
                'content' => [
                    'en' => 'Kristohanon is a private, bilingual Catholic Scripture companion created for Filipino life. It helps a visitor name what they carry, encounter a relevant reviewed passage, receive a short reflection and prayer, and continue into the Rosary or traditional Catholic prayers. It welcomes other Christians while remaining honest about its Catholic devotional roots. It is an AI tool, not Jesus, divine revelation, clergy, therapy, or emergency care.',
                    'ceb' => 'Ang Kristohanon usa ka pribado ug bilingual nga Katolikong kauban sa Kasulatan nga gihimo alang sa kinabuhi sa mga Pilipino. Motabang kini sa usa ka tawo sa pagsulti sa iyang gidala, pagtagbo sa angay ug gisusi nga teksto sa Kasulatan, pagdawat og mubo nga pagpamalandong ug pag-ampo, ug pagpadayon ngadto sa Rosaryo o tradisyonal nga mga pag-ampo. Giabiabi niini ang ubang Kristohanon samtang matinud-anon sa Katolikong devotional roots. AI tool kini, dili si Hesus, divine revelation, pari, therapy, o emergency care.',
                ],
                'keywords' => ['kristohanon', 'this app', 'this site', 'website', 'what is', 'purpose', 'about'],
                'url' => '/about',
            ],
            [
                'id' => 'author-testimony',
                'title' => ['en' => 'The testimony behind Kristohanon', 'ceb' => 'Ang pagpamatuod luyo sa Kristohanon'],
                'content' => [
                    'en' => 'Kristohanon grew from the first-person experience of a Catholic virtual assistant living in Southern Leyte. Even while working, he struggled financially and felt that life was standing still. He eventually surrendered his fear, plans, work, and finances to God—not by abandoning responsibility, but by releasing the belief that he could control everything. As he slowed down to read and understand Scripture, he became less ruled by fear and a little closer to God. Opportunities then began arriving even when he was not actively applying for jobs. He received them as grace, not as a formula or proof that faith guarantees wealth. The deeper miracle was peace, gratitude, and recognizing God’s provision. He created Kristohanon so others in stagnant or difficult seasons could begin with God and encounter Scripture in words they understand.',
                    'ceb' => 'Ang Kristohanon mitubo gikan sa personal nga kasinatian sa usa ka Katolikong virtual assistant nga nagpuyo sa Southern Leyte. Bisan nagtrabaho siya, naglisod gihapon siya sa pinansyal ug mibati nga daw wala mouswag ang kinabuhi. Sa ulahi iyang gitugyan ngadto sa Dios ang kahadlok, mga plano, trabaho, ug pinansya—dili pinaagi sa pagbiya sa responsibilidad, kondili pinaagi sa pagbuhi sa pagtuo nga makontrol niya ang tanan. Sa iyang hinay nga pagbasa ug pagsabot sa Kasulatan, dili na kaayo siya gimandoan sa kahadlok ug nahimong mas duol gamay sa Dios. Misugod dayong moabot ang mga oportunidad bisan wala siya aktibong nangapply. Gidawat niya kini isip grasya, dili pormula o saad nga ang pagtuo awtomatikong maghatag og bahandi. Ang mas lawom nga milagro mao ang kalinaw, pagpasalamat, ug pag-ila sa pag-atiman sa Dios. Gihimo niya ang Kristohanon aron ang uban nga anaa sa lisod o stagnant nga panahon makasugod uban sa Dios ug makatagbo sa Kasulatan sa pinulongang ilang masabtan.',
                ],
                'keywords' => ['author', 'creator', 'founder', 'testimony', 'story', 'southern leyte', 'virtual assistant', 'va', 'financial', 'opportunities', 'miracle', 'why created'],
                'url' => '/about',
            ],
            [
                'id' => 'privacy-and-ai',
                'title' => ['en' => 'Privacy and AI boundaries', 'ceb' => 'Privacy ug mga limitasyon sa AI'],
                'content' => [
                    'en' => 'Core features require no account. Personal prayer activity, Rosary progress, gratitude text, quiz state, and recent prayer information are stored only in the visitor’s browser. The site does not save the conversation message in its own database, but a message submitted for guidance is transmitted to the configured AI provider to compose the response. Kristohanon retrieves reviewed local passages first and renders verified Scripture separately; the model is instructed not to invent Scripture.',
                    'ceb' => 'Walay account nga gikinahanglan sa core features. Ang prayer activity, progress sa Rosaryo, gratitude text, quiz state, ug recent prayer information gitipigan lamang sa browser sa bisita. Dili tipigan sa site ang mensahe sa kaugalingong database, apan ang mensaheng gipadala alang sa guidance ipadala ngadto sa configured AI provider aron mohimo og tubag. Mangita una ang Kristohanon og gisusi nga local passages ug ipakita ang verified Scripture nga bulag; gitudloan ang model nga dili mag-imbento og Kasulatan.',
                ],
                'keywords' => ['privacy', 'private', 'saved', 'store', 'data', 'openai', 'ai', 'database', 'message'],
                'url' => '/about',
            ],
            [
                'id' => 'translations',
                'title' => ['en' => 'Bible translations and sources', 'ceb' => 'Mga hubad ug gigikanan sa Biblia'],
                'content' => [
                    'en' => 'English Scripture uses the World English Bible Updated, a modern American-English public-domain translation. Cebuano Scripture uses Biblica Open Ang Pulong sa Dios 2024 under CC BY-SA 4.0 with attribution. Scripture quotations are selected from the reviewed local library rather than generated from model memory.',
                    'ceb' => 'Ang English Scripture naggamit sa World English Bible Updated, usa ka modernong American-English public-domain translation. Ang Cebuano Scripture naggamit sa Biblica Open Ang Pulong sa Dios 2024 ubos sa CC BY-SA 4.0 nga adunay attribution. Ang mga kinutlo sa Kasulatan pilion gikan sa gisusi nga local library ug dili himoon gikan sa memorya sa model.',
                ],
                'keywords' => ['translation', 'english bible', 'cebuano bible', 'source', 'license', 'world english bible', 'open ang pulong sa dios'],
                'url' => '/about',
            ],
            [
                'id' => 'site-guide',
                'title' => ['en' => 'What you can do here', 'ceb' => 'Unsay mahimo dinhi'],
                'content' => [
                    'en' => 'Home offers the Scripture conversation, a seven-day verse reader, Lectio Divina, gratitude, a daily Catholic question, Church calendar context, and a private prayer rhythm. Rosary provides a complete bead-by-bead bilingual guide with day-appropriate mysteries and local progress. Prayers provides a searchable bilingual library grouped by purpose. About contains the creator’s long-form testimony and source information.',
                    'ceb' => 'Ang Home adunay Scripture conversation, seven-day verse reader, Lectio Divina, gratitude, daily Catholic question, Church calendar context, ug private prayer rhythm. Ang Rosary adunay kompleto nga bilingual bead-by-bead guide uban sa mystery nga angay sa adlaw ug local progress. Ang Prayers adunay searchable bilingual library nga gi-grupo sumala sa purpose. Ang About adunay taas nga personal nga pagpamatuod sa naghimo ug source information.',
                ],
                'keywords' => ['features', 'use app', 'what can', 'home', 'rosary page', 'prayers page', 'about page', 'how to use'],
                'url' => '/',
            ],
        ];

        $manilaToday = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Manila'));
        $todayMysteryKey = $rosary['schedule'][(int) $manilaToday->format('N')];
        $todayMystery = $rosary['mysteries'][$todayMysteryKey];
        $entries[] = [
            'id' => 'rosary-today',
            'title' => ['en' => 'Today’s Rosary mysteries', 'ceb' => 'Mga misteryo sa Rosaryo karong adlawa'],
            'content' => [
                'en' => 'In Asia/Manila, today is ' . $manilaToday->format('l, F j, Y') . '. The traditional set is the ' . $todayMystery['en']['name'] . '. The guided Rosary page selects it automatically.',
                'ceb' => 'Sa Asia/Manila, karon kay ' . $manilaToday->format('F j, Y') . '. Ang tradisyonal nga set mao ang ' . $todayMystery['ceb']['name'] . '. Awtomatikong pilion kini sa guided Rosary page.',
            ],
            'keywords' => ['today mystery', 'mysteries today', 'misteryo karon', 'today rosary', 'rosaryo karon'],
            'url' => '/rosary',
        ];

        foreach ($rosary['mysteries'] as $key => $mystery) {
            foreach (['en', 'ceb'] as $language) {
                $items = array_map(
                    static fn (array $item, int $index): string => ($index + 1) . '. ' . $item[0] . ' (' . $item[1] . ')',
                    $mystery[$language]['items'],
                    array_keys($mystery[$language]['items'])
                );
                $mysteryContent[$language] = $mystery[$language]['name'] . ' — ' . $mystery[$language]['days'] . ': ' . implode('; ', $items) . '.';
                $mysteryTitle[$language] = $mystery[$language]['name'];
            }
            $entries[] = [
                'id' => 'mystery-' . $key,
                'title' => $mysteryTitle,
                'content' => $mysteryContent,
                'keywords' => [$key, $mystery['en']['name'], $mystery['ceb']['name']],
                'url' => '/rosary',
            ];
        }

        foreach ($prayers as $prayer) {
            $entries[] = [
                'id' => 'prayer-' . $prayer['id'],
                'title' => [
                    'en' => $prayer['prayer']['en']['title'],
                    'ceb' => $prayer['prayer']['ceb']['title'],
                ],
                'content' => [
                    'en' => $prayer['when']['en'] . ".\n\n" . $prayer['prayer']['en']['text'],
                    'ceb' => $prayer['when']['ceb'] . ".\n\n" . $prayer['prayer']['ceb']['text'],
                ],
                'keywords' => array_values(array_filter(array_merge(
                    preg_split('/\s+/u', $prayer['keywords']) ?: [],
                    preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($prayer['prayer']['en']['title'])) ?: [],
                    preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($prayer['prayer']['ceb']['title'])) ?: [],
                    [$prayer['id'], $prayer['prayer']['en']['title'], $prayer['prayer']['ceb']['title']]
                ))),
                'url' => '/prayers#' . $prayer['id'],
            ];
        }

        return $entries;
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower($value)) ?? mb_strtolower($value));
    }
}
