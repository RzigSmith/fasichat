<?php
/**
 * Classe AudioFichier
 * Gère les messages vocaux et fichiers audio.
 * Concept POO : héritage de Fichier.
 */
class AudioFichier extends Fichier
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
            str_contains($this->typeMime, 'mpeg') => 'mp3',
            str_contains($this->typeMime, 'ogg')  => 'ogg',
            str_contains($this->typeMime, 'wav')  => 'wav',
            str_contains($this->typeMime, 'webm') => 'webm',
            default => 'mp3',
        };
    }
}
