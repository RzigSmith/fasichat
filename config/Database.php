<?php
/**
 * Classe BaseDeDonnees — Singleton PDO
 * Gère la connexion unique à la base de données SQLite via PDO.
 */
class BaseDeDonnees
{
    private static ?BaseDeDonnees $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $driver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';

        if ($driver === 'mysql') {
            $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
            $port = defined('DB_PORT') ? DB_PORT : '3306';
            $dbname = defined('DB_NAME') ? DB_NAME : 'fasichat_classroom';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $options);
            // ensure proper charset
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } else {
            $dsn = 'sqlite:' . DB_PATH;
            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec('PRAGMA foreign_keys = ON;');
            $this->pdo->exec('PRAGMA journal_mode = WAL;');
        }
    }

    /** Empêche le clonage */
    private function __clone() {}

    /** Retourne l'instance unique */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Retourne l'objet PDO */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * Exécute une requête préparée et retourne le PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retourne la dernière clé d'insertion
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Initialise la base de données depuis le fichier schema.sql
     */
    public function initialiser(): void
    {
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->pdo->exec($schema);
    }
}
