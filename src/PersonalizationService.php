<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class PersonalizationService
{
    private const DEFAULT_TOPICS = ['peace', 'hope', 'strength', 'wisdom', 'gratitude'];

    public static function preferences(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT language, topics, email_digest, quiet_hours_start, quiet_hours_end FROM user_preferences WHERE user_id = ?');
        $statement->execute([$userId]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return ['language' => 'en', 'topics' => self::DEFAULT_TOPICS, 'email_digest' => 'off', 'quiet_hours_start' => '21:00', 'quiet_hours_end' => '07:00'];
        }

        $topics = json_decode((string) $row['topics'], true);
        $row['topics'] = is_array($topics) && $topics !== [] ? array_values(array_filter($topics, 'is_string')) : self::DEFAULT_TOPICS;
        return $row;
    }

    public static function savePreferences(int $userId, string $language, array $topics, string $emailDigest): void
    {
        $allowedTopics = array_column(self::availableTopics(), 'id');
        $topics = array_values(array_intersect($allowedTopics, $topics));
        if ($topics === []) {
            $topics = self::DEFAULT_TOPICS;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO user_preferences (user_id, language, topics, email_digest) VALUES (?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE language = VALUES(language), topics = VALUES(topics), email_digest = VALUES(email_digest), updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([$userId, $language === 'ceb' ? 'ceb' : 'en', json_encode($topics), in_array($emailDigest, ['off', 'daily', 'weekly'], true) ? $emailDigest : 'off']);
    }

    public static function record(int $userId, string $type, string $contentId, string $topic = '', int $weight = 1): void
    {
        $allowed = ['view', 'save', 'helpful', 'dismiss', 'pray', 'complete'];
        if (!in_array($type, $allowed, true)) {
            return;
        }

        $statement = Database::connection()->prepare('INSERT INTO user_interactions (user_id, interaction_type, content_id, topic, weight) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$userId, $type, mb_substr($contentId, 0, 120), mb_substr($topic, 0, 60), max(-3, min(5, $weight))]);
    }

    public static function feed(int $userId, int $limit = 12, string $afterId = ''): array
    {
        $preferences = self::preferences($userId);
        $passages = require dirname(__DIR__) . '/data/passages.php';
        $scores = [];

        $statement = Database::connection()->prepare(
            "SELECT topic, SUM(weight) AS affinity FROM user_interactions WHERE user_id = ? AND interaction_type IN ('save','helpful','pray','complete') AND topic <> '' AND created_at >= DATE_SUB(NOW(), INTERVAL 120 DAY) GROUP BY topic"
        );
        $statement->execute([$userId]);
        foreach ($statement->fetchAll() as $row) {
            $scores[(string) $row['topic']] = ($scores[(string) $row['topic']] ?? 0) + (int) $row['affinity'];
        }

        $language = $preferences['language'];
        $candidates = array_values(array_filter($passages, static fn(array $passage): bool => ($passage['language'] ?? '') === $language));
        $matching = array_values(array_filter($candidates, static fn(array $passage): bool => self::matchesPreferences((string) ($passage['topic'] ?? ''), $preferences['topics'])));
        $dailyPool = $matching !== [] ? $matching : $candidates;
        $manilaDate = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Manila'));
        $dailyIndex = count($dailyPool) > 0 ? ((int) $manilaDate->format('z') % count($dailyPool)) : 0;
        $dailyId = $dailyPool[$dailyIndex]['id'] ?? '';

        $ranked = [];
        foreach ($candidates as $index => $passage) {
            $topic = (string) ($passage['topic'] ?? 'hope');
            $preferenceScore = self::matchesPreferences($topic, $preferences['topics']) ? 5 : 0;
            $score = $preferenceScore + ($scores[$topic] ?? 0) + (($passage['id'] === $dailyId) ? 100 : 0) + (($index * 17) % 7) / 10;
            $passage['score'] = $score;
            $passage['is_daily'] = $passage['id'] === $dailyId;
            $passage['image'] = self::imageForTopic($topic);
            $ranked[] = $passage;
        }

        usort($ranked, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        $start = 0;
        if ($afterId !== '') {
            foreach ($ranked as $index => $passage) {
                if (hash_equals((string) $passage['id'], $afterId)) {
                    $start = $index + 1;
                    break;
                }
            }
            if ($start === 0) {
                return [];
            }
        }

        return array_slice($ranked, $start, max(1, min(30, $limit)) + 1);
    }

    private static function matchesPreferences(string $passageTopic, array $preferences): bool
    {
        $groups = [
            'peace' => ['peace', 'trust', 'comfort', 'care', 'rest'],
            'hope' => ['hope', 'resilience', 'endurance', 'purpose', 'return'],
            'strength' => ['courage', 'endurance', 'exhaustion', 'resilience', 'weakness'],
            'wisdom' => ['wisdom', 'guidance', 'purpose', 'work'],
            'gratitude' => ['gratitude', 'blessing', 'contentment'],
            'fear' => ['fear', 'courage', 'security', 'anxiety', 'storm', 'crisis'],
            'grief' => ['grief', 'comfort', 'suffering', 'healing'],
            'family' => ['relationships', 'love', 'care', 'friendship', 'conflict'],
            'work' => ['work', 'contentment', 'anxiety', 'burden'],
            'forgiveness' => ['forgiveness', 'confession', 'mercy', 'return'],
            'loneliness' => ['friendship', 'presence', 'care', 'love'],
            'guidance' => ['guidance', 'wisdom', 'purpose', 'work', 'trust'],
        ];

        foreach ($preferences as $preference) {
            if (in_array($passageTopic, $groups[$preference] ?? [$preference], true)) {
                return true;
            }
        }

        return false;
    }

    public static function availableTopics(): array
    {
        return [
            ['id' => 'peace', 'label' => 'Peace'], ['id' => 'hope', 'label' => 'Hope'],
            ['id' => 'strength', 'label' => 'Strength'], ['id' => 'wisdom', 'label' => 'Wisdom'],
            ['id' => 'gratitude', 'label' => 'Gratitude'], ['id' => 'fear', 'label' => 'Fear and courage'],
            ['id' => 'grief', 'label' => 'Grief'], ['id' => 'family', 'label' => 'Family'],
            ['id' => 'work', 'label' => 'Work and provision'], ['id' => 'forgiveness', 'label' => 'Forgiveness'],
            ['id' => 'loneliness', 'label' => 'Loneliness'], ['id' => 'guidance', 'label' => 'Guidance'],
        ];
    }

    private static function imageForTopic(string $topic): string
    {
        return match ($topic) {
            'peace', 'fear', 'loneliness' => '/assets/images/hero-still-waters.webp',
            'family', 'forgiveness', 'grief' => '/assets/images/church-calendar.webp',
            'wisdom', 'guidance', 'work' => '/assets/images/lectio-bible.webp',
            default => '/assets/images/rosary-season.webp',
        };
    }
}
