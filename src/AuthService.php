<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use Delight\Auth\Auth;
use PDOException;
use Throwable;

final class AuthService
{
    private static ?Auth $auth = null;

    public static function available(): bool
    {
        if (!Database::isConfigured()) {
            return false;
        }

        try {
            Database::connection();
            return true;
        } catch (Throwable $error) {
            // Log only database error identifiers; never include credentials or DSN values.
            if ($error instanceof PDOException) {
                $sqlState = (string) ($error->errorInfo[0] ?? $error->getCode() ?: 'unknown');
                $driverCode = (string) ($error->errorInfo[1] ?? 'unknown');
                error_log('Kristohanon database connection failed (SQLSTATE ' . $sqlState . ', driver ' . $driverCode . ').');
            } else {
                error_log('Kristohanon database initialization failed (' . get_class($error) . ').');
            }
            return false;
        }
    }

    public static function auth(): Auth
    {
        if (!self::$auth instanceof Auth) {
            self::$auth = new Auth(Database::connection(), null, '', true, 300);
        }

        return self::$auth;
    }

    public static function userId(): ?int
    {
        if (!self::available() || !self::auth()->isLoggedIn()) {
            return null;
        }

        return (int) self::auth()->getUserId();
    }
}
