<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function isConfigured(): bool
    {
        return Config::get('DB_HOST') !== '' && Config::get('DB_NAME') !== '' && Config::get('DB_USER') !== '';
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        if (!self::isConfigured()) {
            throw new PDOException('The Kristohanon account database is not configured.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            Config::get('DB_HOST'),
            (int) Config::get('DB_PORT', '3306'),
            Config::get('DB_NAME')
        );

        self::$connection = new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }
}
