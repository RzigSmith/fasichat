<?php
/**
 * Classe Valve
 * Espace dédié aux annonces institutionnelles.
 * Seul l'Apparitaire peut publier/modifier/supprimer.
 * Tous les autres utilisateurs peuvent consulter en lecture seule.
 * Concept POO : contrôle d'accès explicite, CRUD encapsulé.
 */
class Valve
{
    /**
     * Ajoute une annonce sur le Valve.
     * Vérifie que l'auteur est bien un Apparitaire.
     */
    public function ajouterAnnonce(
        string  $titre,
        string  $contenu,
        int     $auteurId,
        ?string $dateExpiration = null
    ): int {
        $this->verifierDroitEcriture($auteurId);

        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO valve_annonces (titre, contenu, auteur_id, date_publication, date_expiration)
             VALUES (?, ?, ?, datetime(\'now\'), ?)',
            [$titre, $contenu, $auteurId, $dateExpiration]
        );
        return (int) $db->lastInsertId();
    }

    /**
     * Modifie une annonce existante.
     */
    public function modifierAnnonce(
        int     $annonceId,
        string  $titre,
        string  $contenu,
        int     $auteurId,
        ?string $dateExpiration = null
    ): bool {
        $this->verifierDroitEcriture($auteurId);

        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query(
            'UPDATE valve_annonces SET titre = ?, contenu = ?, date_expiration = ? WHERE id = ?',
            [$titre, $contenu, $dateExpiration, $annonceId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Supprime une annonce.
     */
    public function supprimerAnnonce(int $annonceId, int $auteurId): bool
    {
        $this->verifierDroitEcriture($auteurId);

        $db   = BaseDeDonnees::getInstance();
        $stmt = $db->query('DELETE FROM valve_annonces WHERE id = ?', [$annonceId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupère toutes les annonces actives (non expirées).
     */
    public function getAnnonces(): array
    {
        return BaseDeDonnees::getInstance()->query(
            "SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom
             FROM valve_annonces a
             JOIN utilisateurs u ON a.auteur_id = u.id
             WHERE a.date_expiration IS NULL OR a.date_expiration > datetime('now')
             ORDER BY a.date_publication DESC"
        )->fetchAll();
    }

    /**
     * Récupère toutes les annonces (y compris expirées) pour l'Apparitaire.
     */
    public function getToutesAnnonces(): array
    {
        return BaseDeDonnees::getInstance()->query(
            'SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom
             FROM valve_annonces a
             JOIN utilisateurs u ON a.auteur_id = u.id
             ORDER BY a.date_publication DESC'
        )->fetchAll();
    }

    /**
     * Vérifie que l'utilisateur est bien un Apparitaire.
     * Lève une exception si ce n'est pas le cas.
     */
    private function verifierDroitEcriture(int $userId): void
    {
        $db   = BaseDeDonnees::getInstance();
        $user = $db->query('SELECT role FROM utilisateurs WHERE id = ?', [$userId])->fetch();

        if (!$user || $user['role'] !== 'apparitaire') {
            throw new RuntimeException('Accès refusé : seul l\'Apparitaire peut gérer le Valve.');
        }
    }
}
