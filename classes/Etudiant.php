<?php
/**
 * Classe Etudiant — acteur pédagogique
 * Peut envoyer des messages texte, vocaux et fichiers.
 * Communique en privé avec les autres étudiants et en public avec les enseignants.
 */
class Etudiant extends Utilisateur
{
    private int $promotionId;

    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'etudiant',
        string $avatar = '',
        string $dateInscription = '',
        int    $promotionId = 0
    ) {
        parent::__construct($id, $nom, $prenom, $email, $motDePasse, $role, $avatar, $dateInscription);
        $this->promotionId = $promotionId;
    }

    public function getPromotionId(): int { return $this->promotionId; }
    public function setPromotionId(int $id): void { $this->promotionId = $id; }

    /**
     * Les droits d'un étudiant :
     * - envoyer des messages privés aux autres étudiants de sa promo
     * - envoyer des messages publics aux enseignants
     * - partager des fichiers
     * - consulter le Valve (lecture seule)
     */
    public function getDroits(): array
    {
        return [
            'message_prive_etudiants' => true,
            'message_public_enseignant' => true,
            'partager_fichier' => true,
            'consulter_valve' => true,
            'publier_valve' => false,
            'publier_mur' => false,
            'convoquer' => false,
            'voir_liste_etudiants' => false,
        ];
    }
}
