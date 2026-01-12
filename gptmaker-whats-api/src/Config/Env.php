<?php
namespace App\Config;

use Dotenv\Dotenv;

final class Env
{
    public static function load(): void
    {
        $path = dirname(__DIR__, 2);
        if (file_exists($path . '/.env')) {
            $dotenv = Dotenv::createImmutable($path);
            $dotenv->safeLoad();
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $val === false ? $default : $val;
    }
}
