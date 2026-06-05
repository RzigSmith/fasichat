<?php
/**
 * Classe Promotion
 * Représente une promotion d'étudiants (ex: L2 Info 2025-2026).
 */
class Promotion
{
    private int    $id;
    private string $nom;
    private string $annee;

    public function __construct(int $id, string $nom, string $annee)
    {
        $this->id   = $id;
        $this->nom  = $nom;
        $this->annee = $annee;
    }

    public function getId(): int      { return $this->id; }
    public function getNom(): string  { return $this->nom; }
    public function getAnnee(): string { return $this->annee; }

    public function enregistrer(): int
    {
        $db = BaseDeDonnees::getInstance();
        $db->query('INSERT INTO promotions (nom, annee) VALUES (?, ?)', [$this->nom, $this->annee]);
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    public static function toutes(): array
    {
        return BaseDeDonnees::getInstance()->query('SELECT * FROM promotions ORDER BY annee DESC, nom ASC')->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $row = BaseDeDonnees::getInstance()->query('SELECT * FROM promotions WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }
}
