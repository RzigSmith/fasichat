<?php
/**
 * Classe VideoFichier
 * Gère les fichiers vidéo. Pour une compression réelle, ffmpeg serait requis.
 * Ici, on stocke la vidéo telle quelle tout en validant le type MIME.
 * Concept POO : héritage de Fichier.
 */
class VideoFichier extends Fichier
{
    public function traiter(string $tmpPath): bool
    {
        $ext             = $this->detecterExtension();
        $this->nomStocke = $this->genererNomStocke($ext);
        $destination     = UPLOAD_DIR . $this->nomStocke;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return false;
        }

        $this->chemin = $this->nomStocke;
        $this->taille = filesize($destination);
        return true;
    }

    private function detecterExtension(): string
    {
        return match (true) {
            str_contains($this->typeMime, 'mp4')  => 'mp4',
            str_contains($this->typeMime, 'webm') => 'webm',
            str_contains($this->typeMime, 'ogg')  => 'ogv',
            default => 'mp4',
        };
    }
}
