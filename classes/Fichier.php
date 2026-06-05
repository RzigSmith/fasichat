<?php
/**
 * Classe abstraite Fichier
 * Gère l'upload, la validation et le stockage des fichiers multimédia.
 * Concept POO : encapsulation, classe abstraite.
 * Contrainte : taille maximale 20 Mo, compression automatique pour images/vidéos.
 */
abstract class Fichier
{
    protected int    $id;
    protected string $nomOriginal;
    protected string $nomStocke;
    protected string $typeMime;
    protected int    $taille;
    protected string $chemin;
    protected int    $uploadeurId;
    protected string $dateUpload;

    public function __construct(
        int    $id,
        string $nomOriginal,
        string $nomStocke,
        string $typeMime,
        int    $taille,
        string $chemin,
        int    $uploadeurId,
        string $dateUpload = ''
    ) {
        $this->id          = $id;
        $this->nomOriginal = $nomOriginal;
        $this->nomStocke   = $nomStocke;
        $this->typeMime    = $typeMime;
        $this->taille      = $taille;
        $this->chemin      = $chemin;
        $this->uploadeurId = $uploadeurId;
        $this->dateUpload  = $dateUpload ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int           { return $this->id; }
    public function getNomOriginal(): string { return $this->nomOriginal; }
    public function getNomStocke(): string { return $this->nomStocke; }
    public function getTypeMime(): string  { return $this->typeMime; }
    public function getTaille(): int       { return $this->taille; }
    public function getChemin(): string    { return $this->chemin; }

    /**
     * Traite le fichier après upload (compression, renommage, etc.)
     * Chaque sous-classe implémente sa propre logique.
     */
    abstract public function traiter(string $tmpPath): bool;

    /**
     * Enregistre le fichier en base de données.
     */
    public function enregistrer(): int
    {
        $db = BaseDeDonnees::getInstance();
        $db->query(
            'INSERT INTO fichiers (nom_original, nom_stocke, type_mime, taille, chemin, uploadeur_id, date_upload)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$this->nomOriginal, $this->nomStocke, $this->typeMime, $this->taille, $this->chemin, $this->uploadeurId, $this->dateUpload]
        );
        $this->id = (int) BaseDeDonnees::getInstance()->lastInsertId();
        return $this->id;
    }

    /**
     * Valide le fichier uploadé (type MIME, taille).
     * Lève une exception en cas de violation.
     */
    public static function valider(array $fichierPost, array $typesAutorises): void
    {
        if ($fichierPost['error'] !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite php.ini).',
                UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite formulaire).',
                UPLOAD_ERR_PARTIAL    => 'Upload partiel.',
                UPLOAD_ERR_NO_FILE    => 'Aucun fichier envoyé.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture.',
            ];
            throw new RuntimeException($messages[$fichierPost['error']] ?? 'Erreur inconnue.');
        }

        if ($fichierPost['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('Le fichier dépasse la limite de 20 Mo.');
        }

        // Vérification MIME réelle (pas seulement l'extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $typeMime = $finfo->file($fichierPost['tmp_name']);

        if (!in_array($typeMime, $typesAutorises, true)) {
            throw new RuntimeException("Type de fichier non autorisé : $typeMime");
        }
    }

    /**
     * Factory : crée la bonne instance selon le type MIME.
     */
    public static function creer(array $fichierPost, int $uploadeurId): self
    {
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $typeMime = $finfo->file($fichierPost['tmp_name']);

        if (in_array($typeMime, ALLOWED_TYPES['image'], true)) {
            return new ImageFichier(0, $fichierPost['name'], '', $typeMime, $fichierPost['size'], '', $uploadeurId);
        } elseif (in_array($typeMime, ALLOWED_TYPES['video'], true)) {
            return new VideoFichier(0, $fichierPost['name'], '', $typeMime, $fichierPost['size'], '', $uploadeurId);
        } elseif ($typeMime === 'audio/mpeg' || str_starts_with($typeMime, 'audio/')) {
            return new AudioFichier(0, $fichierPost['name'], '', $typeMime, $fichierPost['size'], '', $uploadeurId);
        } else {
            return new DocumentFichier(0, $fichierPost['name'], '', $typeMime, $fichierPost['size'], '', $uploadeurId);
        }
    }

    /**
     * Génère un nom unique pour le fichier stocké.
     */
    protected function genererNomStocke(string $extension): string
    {
        return uniqid('file_', true) . '.' . ltrim($extension, '.');
    }
}
