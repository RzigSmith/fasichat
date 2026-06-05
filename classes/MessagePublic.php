<?php
/**
 * Classe MessagePublic
 * Message visible par tous les membres de la promotion concernée.
 * Utilisé pour : échange étudiant↔enseignant (public, visible par la promo).
 */
class MessagePublic extends Message
{
    private int $promotionId;
    private ?int $destinataireId;

    public function __construct(
        int    $id,
        string $contenu,
        int    $expediteurId,
        int    $promotionId,
        ?int   $destinataireId = null,
        string $dateEnvoi = '',
        ?int   $fichierId = null
    ) {
        parent::__construct($id, $contenu, $expediteurId, 'public', $dateEnvoi, $fichierId);
        $this->promotionId    = $promotionId;
        $this->destinataireId = $destinataireId;
    }

    public function getPromotionId(): int   { return $this->promotionId; }
    public function getDestinataireid(): ?int { return $this->destinataireId; }

    public function enregistrer(): int
    {
        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO messages (contenu, expediteur_id, destinataire_id, type, promotion_id, date_envoi, fichier_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$this->contenu, $this->expediteurId, $this->destinataireId, 'public', $this->promotionId, $this->dateEnvoi, $this->fichierId]
        );
        $this->id = (int) BaseDeDonnees::getInstance()->lastInsertId();
        return $this->id;
    }

    /**
     * Récupère tous les messages publics d'une promotion.
     */
    public static function recuperer(array $filtres = []): array
    {
        $db          = BaseDeDonnees::getInstance();
        $promotionId = $filtres['promotion_id'] ?? 0;

        return $db->query(
            'SELECT m.*,
                    u.nom AS exp_nom, u.prenom AS exp_prenom, u.avatar AS exp_avatar, u.role AS exp_role,
                    f.nom_original AS fichier_nom, f.type_mime AS fichier_type, f.chemin AS fichier_chemin
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             LEFT JOIN fichiers f ON m.fichier_id = f.id
             WHERE m.type = ? AND m.promotion_id = ?
             ORDER BY m.date_envoi ASC',
            ['public', $promotionId]
        )->fetchAll();
    }
}
