<?php
/**
 * Classe MurPedagogique
 * Espace de publication pour les enseignants et assistants, visible par tous les membres du cours.
 */
class MurPedagogique
{
    /**
     * Publie un message sur le mur d'un cours.
     * Réservé aux enseignants et assistants.
     */
    public function publier(int $auteurId, int $coursId, string $contenu): int
    {
        $this->verifierDroitPublication($auteurId);

        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO mur_pedagogique (auteur_id, cours_id, contenu, date_publication)
             VALUES (?, ?, ?, datetime(\'now\'))',
            [$auteurId, $coursId, $contenu]
        );
        return (int) $db->lastInsertId();
    }

    /**
     * Récupère les publications du mur d'un cours.
     */
    public function getPublications(int $coursId): array
    {
        return BaseDeDonnees::getInstance()->query(
            'SELECT m.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.role AS auteur_role, u.avatar
             FROM mur_pedagogique m
             JOIN utilisateurs u ON m.auteur_id = u.id
             WHERE m.cours_id = ?
             ORDER BY m.date_publication DESC',
            [$coursId]
        )->fetchAll();
    }

    /**
     * Supprime une publication (auteur seulement).
     */
    public function supprimer(int $publicationId, int $auteurId): bool
    {
        $stmt = BaseDeDonnees::getInstance()->query(
            'DELETE FROM mur_pedagogique WHERE id = ? AND auteur_id = ?',
            [$publicationId, $auteurId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Vérifie que l'utilisateur est enseignant ou assistant.
     */
    private function verifierDroitPublication(int $userId): void
    {
        $db   = BaseDeDonnees::getInstance();
        $user = $db->query('SELECT role FROM utilisateurs WHERE id = ?', [$userId])->fetch();

        if (!$user || !in_array($user['role'], ['enseignant', 'assistant'], true)) {
            throw new RuntimeException('Accès refusé : seuls les enseignants et assistants peuvent publier sur le mur pédagogique.');
        }
    }
}
