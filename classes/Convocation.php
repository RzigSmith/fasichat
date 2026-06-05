<?php
/**
 * Classe Convocation — message administratif spécial
 * Envoyée par le Doyen ou le Vice-Doyen à l'ensemble des enseignants et assistants.
 * Concept POO : classe distincte héritant de Message, méthode enregistrer() spécifique.
 */
class Convocation extends Message
{
    private string $objet;
    private string $dateReunion;
    private string $lieu;

    public function __construct(
        int    $id,
        string $objet,
        string $dateReunion,
        string $lieu,
        string $messageExplicatif,
        int    $expediteurId,
        string $dateEnvoi = ''
    ) {
        parent::__construct($id, $messageExplicatif, $expediteurId, 'convocation', $dateEnvoi);
        $this->objet       = $objet;
        $this->dateReunion = $dateReunion;
        $this->lieu        = $lieu;
    }

    public function getObjet(): string       { return $this->objet; }
    public function getDateReunion(): string { return $this->dateReunion; }
    public function getLieu(): string        { return $this->lieu; }

    /**
     * Enregistre la convocation et génère une entrée par destinataire
     * (tous les enseignants et assistants de la plateforme).
     */
    public function enregistrer(): int
    {
        $db = BaseDeDonnees::getInstance();

        // 1. Insérer la convocation
        $db->query(
            'INSERT INTO convocations (objet, date_reunion, lieu, message, expediteur_id, date_envoi)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$this->objet, $this->dateReunion, $this->lieu, $this->contenu, $this->expediteurId, $this->dateEnvoi]
        );
        $convId = (int) $db->lastInsertId();
        $this->id = $convId;

        // 2. Récupérer tous les enseignants et assistants
        $destinataires = $db->query(
            "SELECT id FROM utilisateurs WHERE role IN ('enseignant', 'assistant')"
        )->fetchAll();

        // 3. Créer une ligne destinataire pour chacun
        foreach ($destinataires as $dest) {
            $db->query(
                'INSERT INTO convocation_destinataires (convocation_id, destinataire_id, lu) VALUES (?, ?, 0)',
                [$convId, $dest['id']]
            );
        }

        return $convId;
    }

    /**
     * Récupère les convocations d'un destinataire.
     */
    public static function recuperer(array $filtres = []): array
    {
        $db          = BaseDeDonnees::getInstance();
        $userId      = $filtres['destinataire_id'] ?? 0;

        return $db->query(
            'SELECT c.*, cd.lu,
                    u.nom AS exp_nom, u.prenom AS exp_prenom, u.role AS exp_role
             FROM convocations c
             JOIN convocation_destinataires cd ON c.id = cd.convocation_id
             JOIN utilisateurs u ON c.expediteur_id = u.id
             WHERE cd.destinataire_id = ?
             ORDER BY c.date_envoi DESC',
            [$userId]
        )->fetchAll();
    }

    /**
     * Marque une convocation comme lue.
     */
    public static function marquerLu(int $convId, int $userId): void
    {
        BaseDeDonnees::getInstance()->query(
            'UPDATE convocation_destinataires SET lu = 1 WHERE convocation_id = ? AND destinataire_id = ?',
            [$convId, $userId]
        );
    }
}
