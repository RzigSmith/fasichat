<?php
/**
 * Classe Assistant — acteur pédagogique
 * Partage les privilèges de l'enseignant en matière de visualisation
 * des étudiants et de publication sur le mur pédagogique.
 * Reçoit les convocations de réunion.
 */
class Assistant extends Enseignant
{
    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'assistant',
        string $avatar = '',
        string $dateInscription = ''
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
    }

    /**
     * L'assistant hérite des droits de l'enseignant (héritage POO).
     * On peut surcharger si nécessaire.
     */
    public function getDroits(): array
    {
        $droits = parent::getDroits();
        $droits['recevoir_convocation'] = true;
        return $droits;
    }
}
