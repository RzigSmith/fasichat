<?php
/**
 * Classe Apparitaire — acteur administratif
 * Gère l'onglet Valve (CRUD des annonces institutionnelles).
 * Seul habilité à publier, modifier et supprimer des annonces sur le Valve.
 * Ne participe pas aux conversations pédagogiques ni aux réunions.
 */
class Apparitaire extends Utilisateur
{
    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'apparitaire',
        string $avatar = '',
        string $dateInscription = ''
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
    }

    public function getDroits(): array
    {
        return [
            'consulter_valve' => true,
            'publier_valve'   => true,
            'modifier_valve'  => true,
            'supprimer_valve' => true,
            'message_prive'   => false,
            'convoquer'       => false,
            'publier_mur'     => false,
        ];
    }

    /**
     * Publie une nouvelle annonce sur le Valve.
     */
    public function publierAnnonce(string $titre, string $contenu, ?string $dateExpiration = null): int
    {
        $valve = new Valve();
        return $valve->ajouterAnnonce($titre, $contenu, $this->id, $dateExpiration);
    }

    /**
     * Modifie une annonce existante.
     */
    public function modifierAnnonce(int $annonceId, string $titre, string $contenu, ?string $dateExpiration = null): bool
    {
        $valve = new Valve();
        return $valve->modifierAnnonce($annonceId, $titre, $contenu, $this->id, $dateExpiration);
    }

    /**
     * Supprime une annonce.
     */
    public function supprimerAnnonce(int $annonceId): bool
    {
        $valve = new Valve();
        return $valve->supprimerAnnonce($annonceId, $this->id);
    }
}
