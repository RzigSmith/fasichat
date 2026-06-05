<?php
require_once __DIR__ . '/Convocable.php';

/**
 * Classe ViceDoyen — acteur administratif
 * Adjoint du Doyen. Mêmes droits de convocation.
 * Affecte les enseignants à un ou plusieurs cours.
 * Messages privés avec le Doyen = strictement confidentiels.
 */
class ViceDoyen extends Utilisateur implements Convocable
{
    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'viceDoyen',
        string $avatar = '',
        string $dateInscription = ''
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
    }

    public function getDroits(): array
    {
        return [
            'message_prive_doyen' => true,
            'convoquer' => true,
            'affecter_enseignant' => true,
            'consulter_valve' => true,
            'publier_valve' => false,
            'voir_liste_etudiants' => true,
            'voir_tous_utilisateurs' => true,
        ];
    }

    /**
     * Envoie une convocation collective à tous les enseignants et assistants.
     */
    public function convoquer(string $objet, string $date, string $lieu, string $message = ''): int
    {
        $convocation = new Convocation(0, $objet, $date, $lieu, $message, $this->id);
        return $convocation->enregistrer();
    }

    /**
     * Affecte un enseignant à un cours.
     *
     * @param int $enseignantId ID de l'enseignant
     * @param int $coursId      ID du cours
     */
    public function affecterEnseignant(int $enseignantId, int $coursId): void
    {
        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT OR IGNORE INTO cours_enseignants (cours_id, enseignant_id) VALUES (?, ?)',
            [$coursId, $enseignantId]
        );
    }
}
