<?php
namespace App;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host    = getenv('DB_HOST') ?: 'db';
        $db      = getenv('DB_NAME') ?: 'asistencia_db';
        $user    = getenv('DB_USER') ?: 'asistencia_user';
        $pass    = getenv('DB_PASS') ?: 'UserPass2026!';
        $charset = 'utf8mb4';

        $dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Retry hasta 10 veces con 3s de espera — MySQL puede tardar en arrancar
        $maxRetries = 10;
        $attempt    = 0;
        while (true) {
            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
                break; // Conexión exitosa
            } catch (PDOException $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw new PDOException("No se pudo conectar a la BD tras $maxRetries intentos: " . $e->getMessage(), (int)$e->getCode());
                }
                sleep(3);
            }
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
