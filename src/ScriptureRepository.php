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

    public function find(string $message, string $language, array $excludedIds = [], int $limit = 5): array
    {
        $normalized = $this->normalize($message);
        $tokens = array_values(array_unique(array_filter(
            preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [],
            static fn (string $token): bool => mb_strlen($token) >= 4
        )));
        $scored = [];

        foreach ($this->passages as $passage) {
            if ($passage['language'] !== $language) {
                continue;
            }

            $score = 0;
            foreach ($passage['keywords'] as $keyword) {
                $needle = $this->normalize($keyword);
                if (str_contains($normalized, $needle)) {
                    $score += str_contains($needle, ' ') ? 12 : 8;
                    continue;
                }

                foreach ($tokens as $token) {
                    if (mb_strlen($needle) >= 5 && (str_starts_with($token, $needle) || str_starts_with($needle, $token))) {
                        $score += 3;
                        break;
                    }
                }
            }

            if (in_array($passage['id'], $excludedIds, true)) {
                $score -= 100;
            }

            $scored[] = [
                'score' => $score,
                'tie' => sprintf('%u', crc32($normalized . '|' . $passage['id'])),
                'passage' => $passage,
            ];
        }

        usort($scored, static fn (array $a, array $b): int =>
            ($b['score'] <=> $a['score']) ?: ($a['tie'] <=> $b['tie'])
        );
        return array_map(
            static fn (array $item): array => $item['passage'],
            array_slice($scored, 0, $limit)
        );
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower($value)) ?? mb_strtolower($value));
    }
}

