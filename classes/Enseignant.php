<?php
/**
 * Classe Enseignant — acteur pédagogique
 * Responsable d'un ou plusieurs cours.
 * Peut voir la liste des étudiants, publier sur le mur pédagogique,
 * échanger en privé avec les autres enseignants, recevoir des convocations.
 */
class Enseignant extends Utilisateur
{
    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'enseignant',
        string $avatar = '',
        string $dateInscription = ''
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
    }

    public function getDroits(): array
    {
        return [
            'message_prive_etudiants' => false,
            'message_public_enseignant' => true,
            'message_prive_enseignants' => true,
            'partager_fichier' => true,
            'consulter_valve' => true,
            'publier_valve' => false,
            'publier_mur' => true,
            'convoquer' => false,
            'voir_liste_etudiants' => true,
            'recevoir_convocation' => true,
        ];
    }

    /**
     * Récupère la liste des cours de l'enseignant
     */
    public function getCours(): array
    {
        $db   = BaseDeDonnees::getInstance();
        $stmt = $db->query(
            'SELECT c.* FROM cours c
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             WHERE ce.enseignant_id = ?',
            [$this->id]
        );
        return $stmt->fetchAll();
    }

    /**
     * Récupère les étudiants affiliés aux cours de l'enseignant
     */
    public function getEtudiants(): array
    {
        $db   = BaseDeDonnees::getInstance();
        $stmt = $db->query(
            'SELECT DISTINCT u.* FROM utilisateurs u
             JOIN cours c ON u.promotion_id = c.promotion_id
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             WHERE ce.enseignant_id = ? AND u.role = ?',
            [$this->id, 'etudiant']
        );
        return $stmt->fetchAll();
    }
}
