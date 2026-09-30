<?php
class Database 
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO 
    {
        if(self::$connection === null) {
            $dsn = "mysql:host=". DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            try {
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Koneksi database gagal: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

}