<?php

declare(strict_types=1);

namespace Core;

class Database
{
    /**
     * The single PDO instance (Singleton).
     */
    protected static ?\PDO $connection = null;

    /**
     * Allow instantiation for dependency injection/testing.
     */
    public function __construct() {}

    /**
     * Allow cloning.
     */
    public function __clone() {}

    /**
     * Get the singleton PDO connection.
     *
     * Reads credentials from the .env config (via Core\Config).
     * Configures PDO to throw exceptions and fetch as associative arrays by default.
     */
    public static function getConnection(): \PDO
    {
        if (static::$connection === null) {
            $host = Config::get('DB_HOST', '127.0.0.1');
            $port = Config::get('DB_PORT', '3306');
            $database = Config::get('DB_DATABASE', 'mini_laravel');
            $username = Config::get('DB_USERNAME', 'root');
            $password = Config::get('DB_PASSWORD', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            static::$connection = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE  => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES    => false,
            ]);
        }

        return static::$connection;
    }
}
