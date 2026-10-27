<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class ScriptureRepository
{
    private array $passages;

    public function __construct()
    {
        $this->passages = require dirname(__DIR__) . '/data/passages.php';
    }

    public function find(string $message, string $language, int $limit = 2): array
    {
        $normalized = mb_strtolower($message);
        $scored = [];

        foreach ($this->passages as $passage) {
            if ($passage['language'] !== $language) {
                continue;
            }

            $score = 0;
            foreach ($passage['keywords'] as $keyword) {
                if (str_contains($normalized, mb_strtolower($keyword))) {
                    $score += 3;
                }
            }

            if ($passage['topic'] === 'peace') {
                $score += 1;
            }

            $scored[] = ['score' => $score, 'passage' => $passage];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
        return array_map(
            static fn (array $item): array => $item['passage'],
            array_slice($scored, 0, $limit)
        );
    }
}

