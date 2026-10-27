<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class Config
{
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            self::$values[$key] = trim($value, "\"'");
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        $serverValue = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if (is_string($serverValue) && $serverValue !== '') {
            return $serverValue;
        }

        return self::$values[$key] ?? $default;
    }
}

