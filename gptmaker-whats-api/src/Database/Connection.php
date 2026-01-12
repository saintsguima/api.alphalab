<?php
namespace App\Database;

use App\Config\Env;
use PDO;

final class Connection
{
    public static function make(): PDO
    {
        $host    = Env::get('DB_HOST');
        $db      = Env::get('DB_NAME');
        $user    = Env::get('DB_USER');
        $pass    = Env::get('DB_PASS');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }
}
