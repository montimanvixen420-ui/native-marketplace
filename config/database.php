<?php

class Database
{
    private static ?PDO $instance = null;

    // ── I-edit ito base sa setup mo sa XAMPP ──
    private static string $host = '127.0.0.1';
    private static string $port = '3306';
    private static string $dbname = 'tinda_marketplace'; // palitan ng pangalan ng database mo
    private static string $username = 'root';
    private static string $password = ''; // default blank sa XAMPP

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Hindi maka-connect sa database: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
