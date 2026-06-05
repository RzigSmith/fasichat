<?php
/**
 * Contrôleur du Mur Pédagogique
 */
class MurController
{
    /**
     * Retourne les publications d'un cours (AJAX).
     */
    public function getPublications(): void
    {
        SessionManager::exigerConnexion();
        header('Content-Type: application/json');

        $coursId = (int)($_GET['cours_id'] ?? 0);
        if (!$coursId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $mur          = new MurPedagogique();
        $publications = $mur->getPublications($coursId);

        echo json_encode(['success' => true, 'publications' => $publications]);
        exit;
    }

    /**
     * Publie sur le mur pédagogique.
     */
    public function publier(): void
    {
        SessionManager::exigerRole(['enseignant', 'assistant']);
        header('Content-Type: application/json');

        $userId  = SessionManager::getUserId();
        $coursId = (int)($_POST['cours_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');

        if (!$coursId || !$contenu) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes.']);
            exit;
        }

        try {
            $mur = new MurPedagogique();
            $id  = $mur->publier($userId, $coursId, htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'));
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
