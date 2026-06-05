<?php
/**
 * Classe Cours
 * Représente un cours lié à une promotion.
 */
class Cours
{
    private int    $id;
    private string $titre;
    private int    $promotionId;

    public function __construct(int $id, string $titre, int $promotionId)
    {
        $this->id          = $id;
        $this->titre       = $titre;
        $this->promotionId = $promotionId;
    }

    public function getId(): int          { return $this->id; }
    public function getTitre(): string    { return $this->titre; }
    public function getPromotionId(): int { return $this->promotionId; }

    public function enregistrer(): int
    {
        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO cours (titre, promotion_id) VALUES (?, ?)',
            [$this->titre, $this->promotionId]
        );
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    public static function tous(): array
    {
        return BaseDeDonnees::getInstance()->query(
            'SELECT c.*, p.nom AS promotion_nom FROM cours c JOIN promotions p ON c.promotion_id = p.id ORDER BY c.titre'
        )->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $row = BaseDeDonnees::getInstance()->query('SELECT * FROM cours WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }
}
