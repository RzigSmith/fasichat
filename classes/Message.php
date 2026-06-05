<?php
/**
 * Classe abstraite Message
 * Représente un message dans le système FasiChat.
 * Concept POO : classe abstraite, polymorphisme.
 */
abstract class Message
{
    protected int    $id;
    protected string $contenu;
    protected int    $expediteurId;
    protected string $type; // 'prive' | 'public' | 'mur' | 'convocation'
    protected string $dateEnvoi;
    protected ?int   $fichierId;

    public function __construct(
        int    $id,
        string $contenu,
        int    $expediteurId,
        string $type,
        string $dateEnvoi = '',
        ?int   $fichierId = null
    ) {
        $this->id           = $id;
        $this->contenu      = $contenu;
        $this->expediteurId = $expediteurId;
        $this->type         = $type;
        $this->dateEnvoi    = $dateEnvoi ?: date('Y-m-d H:i:s');
        $this->fichierId    = $fichierId;
    }

    // Getters
    public function getId(): int           { return $this->id; }
    public function getContenu(): string   { return $this->contenu; }
    public function getExpediteurId(): int { return $this->expediteurId; }
    public function getType(): string      { return $this->type; }
    public function getDateEnvoi(): string { return $this->dateEnvoi; }
    public function getFichierId(): ?int   { return $this->fichierId; }

    /**
     * Retourne le contenu sécurisé contre les XSS.
     */
    public function getContenuSafe(): string
    {
        return htmlspecialchars($this->contenu, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Enregistre le message en base de données.
     * Chaque sous-classe implémente sa propre logique d'insertion.
     */
    abstract public function enregistrer(): int;

    /**
     * Récupère les messages depuis la base de données.
     */
    abstract public static function recuperer(array $filtres = []): array;
}
