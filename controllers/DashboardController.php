<?php
/**
 * Contrôleur du tableau de bord
 * Tableau de bord de l'enseignant : liste des étudiants, mur pédagogique, convocations.
 */
class DashboardController
{
    private BaseDeDonnees $db;

    public function __construct()
    {
        $this->db = BaseDeDonnees::getInstance();
    }

    /**
     * Tableau de bord de l'enseignant/assistant.
     */
    public function enseignant(): void
    {
        SessionManager::exigerRole(['enseignant', 'assistant']);

        $userData = SessionManager::getUser();
        $dbUser   = $this->db->query('SELECT * FROM utilisateurs WHERE id = ?', [$userData['id']])->fetch();
        $prof     = Utilisateur::factory($dbUser);

        /** @var Enseignant $prof */
        $cours       = $prof->getCours();
        $etudiants   = $prof->getEtudiants();
        $convocations = Convocation::recuperer(['destinataire_id' => $userData['id']]);

        $mur         = new MurPedagogique();
        $coursId     = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : (isset($cours[0]) ? $cours[0]['id'] : 0);
        $publications = $coursId ? $mur->getPublications($coursId) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'publier_mur') {
                $contenu = trim($_POST['contenu'] ?? '');
                $cId     = (int)($_POST['cours_id'] ?? 0);
                if ($contenu && $cId) {
                    try {
                        $mur->publier($userData['id'], $cId, htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'));
                        header('Location: ' . BASE_PATH . '/dashboard?cours_id=' . $cId . '&succes=1');
                        exit;
                    } catch (RuntimeException $e) {
                        // silencieux
                    }
                }
            }
        }

        include __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Tableau de bord admin : gestion des comptes (Doyen/Vice-Doyen).
     */
    public function admin(): void
    {
        SessionManager::exigerRole(['doyen', 'viceDoyen']);

        $utilisateurs = $this->db->query(
            'SELECT u.*, p.nom AS promo_nom FROM utilisateurs u LEFT JOIN promotions p ON u.promotion_id = p.id ORDER BY u.role, u.nom'
        )->fetchAll();

        $promotions = Promotion::toutes();
        $cours      = Cours::tous();

        include __DIR__ . '/../views/admin.php';
    }
}
