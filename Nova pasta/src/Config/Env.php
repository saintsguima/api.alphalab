<?php
namespace App\Config;

use Dotenv\Dotenv;

final class Env
{
    public static function load(): void
    {
        $root = dirname(__DIR__, 2);
        if (file_exists($root . '/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->safeLoad();
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $val === false ? $default : $val;
    }
}
