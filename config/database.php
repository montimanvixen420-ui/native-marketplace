<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'mysql-249e0f00-native-marketplace.d.aivencloud.com';
            $port = getenv('DB_PORT') ?: '16857';
            $dbname = getenv('DB_NAME') ?: 'defaultdb';
            $username = getenv('DB_USER') ?: 'avnadmin';
            $password = getenv('DB_PASS') ?: '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_SSL_CA => true,
                ];

                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}