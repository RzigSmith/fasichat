<?php
/**
 * Classe DocumentFichier
 * Gère les documents : PDF, Word, Excel, texte, etc.
 * Concept POO : héritage de Fichier.
 */
class DocumentFichier extends Fichier
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
        return match ($this->typeMime) {
            'application/pdf'    => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'xlsx',
            'text/plain'         => 'txt',
            default              => 'bin',
        };
    }
}
