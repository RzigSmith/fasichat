<?php
/**
 * Contrôleur du Valve
 * Gère les annonces institutionnelles.
 * L'Apparitaire peut faire du CRUD. Les autres peuvent seulement lire.
 */
class ValveController
{
    private Valve $valve;

    public function __construct()
    {
        $this->valve = new Valve();
    }

    /**
     * Affiche les annonces du Valve.
     */
    public function index(): void
    {
        SessionManager::exigerConnexion();

        $user    = SessionManager::getUser();
        $isAdmin = $user['role'] === 'apparitaire';
        $annonces = $isAdmin
            ? $this->valve->getToutesAnnonces()
            : $this->valve->getAnnonces();

        include __DIR__ . '/../views/valve.php';
    }

    /**
     * Publie une nouvelle annonce (Apparitaire uniquement).
     */
    public function publier(): void
    {
        SessionManager::exigerRole(['apparitaire']);

        $erreur = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre          = trim($_POST['titre'] ?? '');
            $contenu        = trim($_POST['contenu'] ?? '');
            $dateExpiration = !empty($_POST['date_expiration']) ? $_POST['date_expiration'] : null;

            if (!$titre || !$contenu) {
                $erreur = 'Le titre et le contenu sont obligatoires.';
            } else {
                try {
                    $userId = SessionManager::getUserId();
                    $this->valve->ajouterAnnonce(
                        htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'),
                        $userId,
                        $dateExpiration
                    );
                    header('Location: ' . BASE_PATH . '/valve?succes=1');
                    exit;
                } catch (RuntimeException $e) {
                    $erreur = $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/form_annonce.php';
    }

    /**
     * Modifie une annonce existante (Apparitaire uniquement).
     */
    public function modifier(): void
    {
        SessionManager::exigerRole(['apparitaire']);

        $annonceId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$annonceId) {
            header('Location: ' . BASE_PATH . '/valve');
            exit;
        }

        $annonce = BaseDeDonnees::getInstance()->query(
            'SELECT * FROM valve_annonces WHERE id = ?', [$annonceId]
        )->fetch();

        if (!$annonce) {
            header('Location: ' . BASE_PATH . '/valve');
            exit;
        }

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre          = trim($_POST['titre'] ?? '');
            $contenu        = trim($_POST['contenu'] ?? '');
            $dateExpiration = !empty($_POST['date_expiration']) ? $_POST['date_expiration'] : null;

            if (!$titre || !$contenu) {
                $erreur = 'Le titre et le contenu sont obligatoires.';
            } else {
                try {
                    $this->valve->modifierAnnonce(
                        $annonceId,
                        htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'),
                        SessionManager::getUserId(),
                        $dateExpiration
                    );
                    header('Location: ' . BASE_PATH . '/valve?succes=1');
                    exit;
                } catch (RuntimeException $e) {
                    $erreur = $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/form_annonce.php';
    }

    /**
     * Supprime une annonce (Apparitaire uniquement).
     */
    public function supprimer(): void
    {
        SessionManager::exigerRole(['apparitaire']);
        header('Content-Type: application/json');

        $annonceId = (int)($_POST['id'] ?? 0);
        if (!$annonceId) {
            echo json_encode(['success' => false]);
            exit;
        }

        try {
            $ok = $this->valve->supprimerAnnonce($annonceId, SessionManager::getUserId());
            echo json_encode(['success' => $ok]);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
