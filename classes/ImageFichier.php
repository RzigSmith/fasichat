<?php
/**
 * Classe ImageFichier
 * Gère les fichiers image avec compression automatique via l'extension GD.
 * Concept POO : héritage de Fichier, encapsulation de la compression GD.
 */
class ImageFichier extends Fichier
{
    private int $qualiteJpeg = 75; // Qualité JPEG (0-100)
    private int $largeurMax  = 1920;
    private int $hauteurMax  = 1080;

    public function traiter(string $tmpPath): bool
    {
        $ext           = $this->detecterExtension();
        $this->nomStocke = $this->genererNomStocke($ext);
        $destination   = UPLOAD_DIR . $this->nomStocke;

        try {
            $imageSource = $this->chargerImage($tmpPath);
            if ($imageSource === false) {
                // Copie directe si GD ne peut pas traiter
                return move_uploaded_file($tmpPath, $destination);
            }

            $imageSource = $this->redimensionnerSiNecessaire($imageSource);
            $this->sauvegarder($imageSource, $destination, $ext);
            imagedestroy($imageSource);

            $this->chemin = $this->nomStocke;
            $this->taille = filesize($destination);
            return true;
        } catch (Exception $e) {
            error_log('ImageFichier::traiter() - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Charge l'image source avec GD selon son type MIME.
     */
    private function chargerImage(string $path): \GdImage|false
    {
        return match (true) {
            str_contains($this->typeMime, 'jpeg') => imagecreatefromjpeg($path),
            str_contains($this->typeMime, 'png')  => imagecreatefrompng($path),
            str_contains($this->typeMime, 'gif')  => imagecreatefromgif($path),
            str_contains($this->typeMime, 'webp') => imagecreatefromwebp($path),
            default => false,
        };
    }

    /**
     * Redimensionne l'image si elle dépasse les dimensions maximales.
     */
    private function redimensionnerSiNecessaire(\GdImage $image): \GdImage
    {
        $largeur = imagesx($image);
        $hauteur = imagesy($image);

        if ($largeur <= $this->largeurMax && $hauteur <= $this->hauteurMax) {
            return $image;
        }

        $ratio       = min($this->largeurMax / $largeur, $this->hauteurMax / $hauteur);
        $nouvLargeur = (int) round($largeur * $ratio);
        $nouvHauteur = (int) round($hauteur * $ratio);

        $nouvelleImage = imagecreatetruecolor($nouvLargeur, $nouvHauteur);

        // Préserver la transparence pour PNG
        if (str_contains($this->typeMime, 'png')) {
            imagealphablending($nouvelleImage, false);
            imagesavealpha($nouvelleImage, true);
            $transparent = imagecolorallocatealpha($nouvelleImage, 255, 255, 255, 127);
            imagefilledrectangle($nouvelleImage, 0, 0, $nouvLargeur, $nouvHauteur, $transparent);
        }

        imagecopyresampled($nouvelleImage, $image, 0, 0, 0, 0, $nouvLargeur, $nouvHauteur, $largeur, $hauteur);
        imagedestroy($image);
        return $nouvelleImage;
    }

    /**
     * Sauvegarde l'image au format approprié.
     */
    private function sauvegarder(\GdImage $image, string $chemin, string $ext): void
    {
        match ($ext) {
            'jpg', 'jpeg' => imagejpeg($image, $chemin, $this->qualiteJpeg),
            'png'         => imagepng($image, $chemin, 6),
            'gif'         => imagegif($image, $chemin),
            'webp'        => imagewebp($image, $chemin, $this->qualiteJpeg),
            default       => imagejpeg($image, $chemin, $this->qualiteJpeg),
        };
    }

    private function detecterExtension(): string
    {
        return match (true) {
            str_contains($this->typeMime, 'jpeg') => 'jpg',
            str_contains($this->typeMime, 'png')  => 'png',
            str_contains($this->typeMime, 'gif')  => 'gif',
            str_contains($this->typeMime, 'webp') => 'webp',
            default => 'jpg',
        };
    }
}
