<?php

namespace App\Core;

class Database
{
    private static ?\PDO $pdo = null;

    public static function connect(array $config): \PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['db_host'],
            $config['db_name'],
            $config['db_charset']
        );

        self::$pdo = new \PDO($dsn, $config['db_user'], $config['db_pass'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}
