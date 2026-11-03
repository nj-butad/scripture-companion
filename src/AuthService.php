<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use Delight\Auth\Auth;
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
        } catch (Throwable) {
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
