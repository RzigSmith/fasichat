<?php
/**
 * Classe abstraite Utilisateur
 * Classe de base pour tous les acteurs du système FasiChat Classroom.
 * Concept POO : classe abstraite, encapsulation.
 */
abstract class Utilisateur
{
    protected int $id;
    protected string $nom;
    protected string $prenom;
    protected string $email;
    protected string $motDePasse;
    protected string $role;
    protected string $avatar;
    protected string $dateInscription;

    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role,
        string $avatar = '',
        string $dateInscription = ''
    ) {
        $this->id              = $id;
        $this->nom             = $nom;
        $this->prenom          = $prenom;
        $this->email           = $email;
        $this->motDePasse      = $motDePasse;
        $this->role            = $role;
        $this->avatar          = $avatar;
        $this->dateInscription = $dateInscription ?: date('Y-m-d H:i:s');
    }

    // --- Getters ---
    public function getId(): int           { return $this->id; }
    public function getNom(): string       { return $this->nom; }
    public function getPrenom(): string    { return $this->prenom; }
    public function getEmail(): string     { return $this->email; }
    public function getRole(): string      { return $this->role; }
    public function getAvatar(): string    { return $this->avatar; }
    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }

    // --- Setters ---
    public function setNom(string $nom): void       { $this->nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); }
    public function setPrenom(string $prenom): void { $this->prenom = htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8'); }
    public function setEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email invalide : $email");
        }
        $this->email = $email;
    }
    public function setAvatar(string $avatar): void { $this->avatar = $avatar; }

    /**
     * Vérifie si le mot de passe fourni correspond au hash stocké.
     */
    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasse);
    }

    /**
     * Méthode abstraite : chaque type d'utilisateur définit ses droits d'accès.
     */
    abstract public function getDroits(): array;

    /**
     * Représentation textuelle de l'utilisateur.
     */
    public function __toString(): string
    {
        return "[{$this->role}] {$this->getNomComplet()} <{$this->email}>";
    }

    /**
     * Crée une instance Utilisateur concrète à partir d'un tableau de données (factory).
     */
    public static function factory(array $data): self
    {
        $role = $data['role'] ?? '';
        $args = [
            (int) $data['id'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['mot_de_passe'],
            $role,
            $data['avatar'] ?? '',
            $data['date_inscription'] ?? '',
        ];

        return match ($role) {
            'etudiant'    => new Etudiant(...$args, promotionId: (int) ($data['promotion_id'] ?? 0)),
            'enseignant'  => new Enseignant(...$args),
            'assistant'   => new Assistant(...$args),
            'doyen'       => new Doyen(...$args),
            'viceDoyen'   => new ViceDoyen(...$args),
            'apparitaire' => new Apparitaire(...$args),
            default       => throw new InvalidArgumentException("Rôle inconnu : $role"),
        };
    }
}
