<?php
/**
 * Classe MessagePrive
 * Message visible uniquement par l'expéditeur et le destinataire.
 * Utilisé pour : étudiant↔étudiant (même promo), enseignant↔enseignant,
 *               Doyen↔Vice-Doyen (strictement confidentiel).
 */
class MessagePrive extends Message
{
    private int $destinataireId;

    public function __construct(
        int    $id,
        string $contenu,
        int    $expediteurId,
        int    $destinataireId,
        string $dateEnvoi = '',
        ?int   $fichierId = null
    ) {
        parent::__construct($id, $contenu, $expediteurId, 'prive', $dateEnvoi, $fichierId);
        $this->destinataireId = $destinataireId;
    }

    public function getDestinataireid(): int { return $this->destinataireId; }

    /**
     * Vérifie les règles de visibilité avant l'enregistrement.
     * Lève une exception si la combinaison de rôles n'est pas autorisée.
     */
    public function verifierRegles(string $roleExp, string $roleDest): void
    {
        $autorises = [
            ['etudiant',    'etudiant'],
            ['enseignant',  'enseignant'],
            ['enseignant',  'assistant'],
            ['assistant',   'enseignant'],
            ['assistant',   'assistant'],
            ['doyen',       'viceDoyen'],
            ['viceDoyen',   'doyen'],
        ];

        $paire = [$roleExp, $roleDest];
        $ok = false;
        foreach ($autorises as $a) {
            if ($a === $paire) { $ok = true; break; }
        }
        if (!$ok) {
            throw new RuntimeException("Message privé non autorisé entre $roleExp et $roleDest.");
        }
    }

    public function enregistrer(): int
    {
        $db   = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO messages (contenu, expediteur_id, destinataire_id, type, date_envoi, fichier_id)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$this->contenu, $this->expediteurId, $this->destinataireId, 'prive', $this->dateEnvoi, $this->fichierId]
        );
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    /**
     * Récupère les messages privés entre deux utilisateurs.
     */
    public static function recuperer(array $filtres = []): array
    {
        $db       = BaseDeDonnees::getInstance();
        $userId1  = $filtres['user1'] ?? 0;
        $userId2  = $filtres['user2'] ?? 0;

        return $db->query(
            'SELECT m.*, 
                    u1.nom AS exp_nom, u1.prenom AS exp_prenom, u1.avatar AS exp_avatar, u1.role AS exp_role,
                    u2.nom AS dest_nom, u2.prenom AS dest_prenom,
                    f.nom_original AS fichier_nom, f.type_mime AS fichier_type, f.chemin AS fichier_chemin
             FROM messages m
             JOIN utilisateurs u1 ON m.expediteur_id = u1.id
             JOIN utilisateurs u2 ON m.destinataire_id = u2.id
             LEFT JOIN fichiers f ON m.fichier_id = f.id
             WHERE m.type = ? 
               AND ((m.expediteur_id = ? AND m.destinataire_id = ?)
                 OR (m.expediteur_id = ? AND m.destinataire_id = ?))
             ORDER BY m.date_envoi ASC',
            ['prive', $userId1, $userId2, $userId2, $userId1]
        )->fetchAll();
    }
}
