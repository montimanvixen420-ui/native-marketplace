<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Titingnan kung ang app ay tumatakbo sa Localhost
            $hostHeader = $_SERVER['HTTP_HOST'] ?? '';
            $isLocalhost = in_array($hostHeader, ['localhost', '127.0.0.1']) 
                           || str_contains($hostHeader, '.test');

            if ($isLocalhost) {
                // Settings para sa XAMPP Localhost
                $host     = '127.0.0.1';
                $port     = '3306';
                $dbname   = 'tinda_marketplace';
                $username = 'root';
                $password = '';

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
            } else {
                // Settings para sa Cloud / Remote Production (Aiven/InfinityFree)
                $host     = getenv('DB_HOST') ?: 'mysql-249e0f00-native-marketplace.d.aivencloud.com';
                $port     = getenv('DB_PORT') ?: '16857';
                $dbname   = getenv('DB_NAME') ?: 'defaultdb';
                $username = getenv('DB_USER') ?: 'avnadmin';
                $password = getenv('DB_PASS') ?: '';

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_SSL_CA => true,
                ];
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}