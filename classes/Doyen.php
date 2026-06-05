<?php
require_once __DIR__ . '/Convocable.php';

/**
 * Classe Doyen — acteur administratif
 * Responsable de la faculté. Privilèges les plus élevés.
 * Peut convoquer une réunion officielle (implémente Convocable).
 * Concept POO : interface, méthode convoquer().
 */
class Doyen extends Utilisateur implements Convocable
{
    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'doyen',
        string $avatar = '',
        string $dateInscription = ''
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
    }

    public function getDroits(): array
    {
        return [
            'message_prive_viceDoyen' => true,
            'convoquer' => true,
            'consulter_valve' => true,
            'publier_valve' => false,
            'voir_liste_etudiants' => true,
            'voir_tous_utilisateurs' => true,
        ];
    }

    /**
     * Envoie une convocation collective à tous les enseignants et assistants.
     *
     * @param string $objet   Objet de la réunion
     * @param string $date    Date et heure de la réunion (Y-m-d H:i)
     * @param string $lieu    Lieu ou lien de réunion
     * @param string $message Message explicatif facultatif
     * @return int            ID de la convocation créée
     */
    public function convoquer(string $objet, string $date, string $lieu, string $message = ''): int
    {
        $convocation = new Convocation(0, $objet, $date, $lieu, $message, $this->id);
        return $convocation->enregistrer();
    }
}
