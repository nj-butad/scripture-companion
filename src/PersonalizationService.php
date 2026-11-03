<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use PDO;

final class PersonalizationService
{
    private const DEFAULT_TOPICS = ['peace', 'hope', 'strength', 'wisdom', 'gratitude'];

    public static function preferences(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT language, topics, email_digest, quiet_hours_start, quiet_hours_end FROM user_preferences WHERE user_id = ?');
        $statement->execute([$userId]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return ['language' => 'en', 'topics' => self::DEFAULT_TOPICS, 'email_digest' => 'weekly', 'quiet_hours_start' => '21:00', 'quiet_hours_end' => '07:00'];
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
        $statement->execute([$userId, $language === 'ceb' ? 'ceb' : 'en', json_encode($topics), in_array($emailDigest, ['off', 'daily', 'weekly'], true) ? $emailDigest : 'weekly']);
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

    public static function feed(int $userId, int $limit = 18): array
    {
        $preferences = self::preferences($userId);
        $passages = require dirname(__DIR__) . '/data/passages.php';
        $scores = array_fill_keys($preferences['topics'], 5);

        $statement = Database::connection()->prepare(
            "SELECT topic, SUM(weight) AS affinity FROM user_interactions WHERE user_id = ? AND topic <> '' AND created_at >= DATE_SUB(NOW(), INTERVAL 120 DAY) GROUP BY topic"
        );
        $statement->execute([$userId]);
        foreach ($statement->fetchAll() as $row) {
            $scores[(string) $row['topic']] = ($scores[(string) $row['topic']] ?? 0) + (int) $row['affinity'];
        }

        $seenStatement = Database::connection()->prepare("SELECT content_id FROM user_interactions WHERE user_id = ? AND interaction_type = 'view' AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)");
        $seenStatement->execute([$userId]);
        $seen = array_fill_keys($seenStatement->fetchAll(PDO::FETCH_COLUMN), true);

        $language = $preferences['language'];
        $ranked = [];
        foreach ($passages as $index => $passage) {
            if (($passage['language'] ?? '') !== $language) {
                continue;
            }
            $topic = (string) ($passage['topic'] ?? 'hope');
            $score = ($scores[$topic] ?? 1) + (isset($seen[$passage['id']]) ? -8 : 3) + (($index * 17 + (int) date('z')) % 7) / 10;
            $passage['score'] = $score;
            $passage['image'] = self::imageForTopic($topic);
            $ranked[] = $passage;
        }

        usort($ranked, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        return array_slice($ranked, 0, max(1, min(30, $limit)));
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
