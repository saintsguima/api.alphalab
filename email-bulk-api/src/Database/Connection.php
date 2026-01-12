<?php
namespace App\Database;

use App\Config\Env;
use PDO;

final class Connection
{
    public static function make(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            Env::get('DB_HOST', 'localhost'),
            Env::get('DB_NAME', ''),
            Env::get('DB_CHARSET', 'utf8mb4')
        );

        $pdo = new PDO($dsn, Env::get('DB_USER', ''), Env::get('DB_PASS', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }
}
