<?php
/**
 * Contrôleur de messagerie
 * Gère l'envoi et la récupération des messages selon les règles de visibilité.
 */
class MessageController
{
    private BaseDeDonnees $db;

    public function __construct()
    {
        $this->db = BaseDeDonnees::getInstance();
    }

    /**
     * Affiche l'interface de chat.
     */
    public function index(): void
    {
        SessionManager::exigerConnexion();
        $user        = SessionManager::getUser();
        $role        = $user['role'];
        $userId      = $user['id'];
        $conversation = null;
        $messages    = [];
        $contacts    = $this->getContacts($userId, $role);
        $selectedId  = isset($_GET['avec']) ? (int)$_GET['avec'] : null;
        $selectedUser = null;

        if ($selectedId) {
            $selectedUser = $this->db->query(
                'SELECT id, nom, prenom, role, avatar FROM utilisateurs WHERE id = ?',
                [$selectedId]
            )->fetch();

            if ($selectedUser) {
                $messages = MessagePrive::recuperer(['user1' => $userId, 'user2' => $selectedId]);
            }
        }

        // Promotion publique (étudiant↔enseignant)
        $messagesPublics = [];
        if ($role === 'etudiant' && $user['promotion_id']) {
            $messagesPublics = MessagePublic::recuperer(['promotion_id' => $user['promotion_id']]);
        }

        include __DIR__ . '/../views/chat.php';
    }

    /**
     * Envoie un message privé.
     */
    public function envoyerPrive(): void
    {
        SessionManager::exigerConnexion();
        header('Content-Type: application/json');

        $user         = SessionManager::getUser();
        $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);
        $contenu      = trim($_POST['contenu'] ?? '');
        $fichierId    = null;

        if (!$destinataireId || (!$contenu && empty($_FILES['fichier']['name']))) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes.']);
            exit;
        }

        // Traitement du fichier si présent
        if (!empty($_FILES['fichier']['name'])) {
            $fichierId = $this->traiterFichier($user['id']);
            if ($fichierId === null && !$contenu) {
                echo json_encode(['success' => false, 'error' => 'Erreur upload fichier.']);
                exit;
            }
        }

        // Vérifier les rôles
        $destUser = $this->db->query('SELECT role FROM utilisateurs WHERE id = ?', [$destinataireId])->fetch();
        if (!$destUser) {
            echo json_encode(['success' => false, 'error' => 'Destinataire introuvable.']);
            exit;
        }

        try {
            $msg = new MessagePrive(0, $contenu ?: '', $user['id'], $destinataireId, '', $fichierId);
            $msg->verifierRegles($user['role'], $destUser['role']);
            $id = $msg->enregistrer();

            $row = $this->db->query(
                'SELECT m.*, u.nom AS exp_nom, u.prenom AS exp_prenom, u.avatar AS exp_avatar, u.role AS exp_role,
                        f.nom_original AS fichier_nom, f.type_mime AS fichier_type, f.chemin AS fichier_chemin
                 FROM messages m JOIN utilisateurs u ON m.expediteur_id = u.id
                 LEFT JOIN fichiers f ON m.fichier_id = f.id
                 WHERE m.id = ?',
                [$id]
            )->fetch();

            echo json_encode(['success' => true, 'message' => $row]);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Envoie un message public (étudiant↔enseignant, visible par la promo).
     */
    public function envoyerPublic(): void
    {
        SessionManager::exigerConnexion();
        header('Content-Type: application/json');

        $user         = SessionManager::getUser();
        $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);
        $promotionId  = (int) ($_POST['promotion_id'] ?? $user['promotion_id'] ?? 0);
        $contenu      = trim($_POST['contenu'] ?? '');

        if (!$promotionId || !$contenu) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes.']);
            exit;
        }

        $msg = new MessagePublic(0, $contenu, $user['id'], $promotionId, $destinataireId ?: null);
        $id  = $msg->enregistrer();

        $row = $this->db->query(
            'SELECT m.*, u.nom AS exp_nom, u.prenom AS exp_prenom, u.avatar AS exp_avatar, u.role AS exp_role
             FROM messages m JOIN utilisateurs u ON m.expediteur_id = u.id WHERE m.id = ?',
            [$id]
        )->fetch();

        echo json_encode(['success' => true, 'message' => $row]);
        exit;
    }

    /**
     * Retourne les nouveaux messages depuis un ID donné (polling AJAX).
     */
    public function nouveauxMessages(): void
    {
        SessionManager::exigerConnexion();
        header('Content-Type: application/json');

        $userId      = SessionManager::getUserId();
        $avecId      = (int)($_GET['avec'] ?? 0);
        $depuisId    = (int)($_GET['depuis'] ?? 0);

        if (!$avecId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $rows = $this->db->query(
            'SELECT m.*, u.nom AS exp_nom, u.prenom AS exp_prenom, u.avatar AS exp_avatar, u.role AS exp_role,
                    f.nom_original AS fichier_nom, f.type_mime AS fichier_type, f.chemin AS fichier_chemin
             FROM messages m JOIN utilisateurs u ON m.expediteur_id = u.id
             LEFT JOIN fichiers f ON m.fichier_id = f.id
             WHERE m.type = ? AND m.id > ?
               AND ((m.expediteur_id = ? AND m.destinataire_id = ?)
                 OR (m.expediteur_id = ? AND m.destinataire_id = ?))
             ORDER BY m.date_envoi ASC',
            ['prive', $depuisId, $userId, $avecId, $avecId, $userId]
        )->fetchAll();

        echo json_encode(['success' => true, 'messages' => $rows]);
        exit;
    }

    /**
     * Construit la liste des contacts accessibles selon le rôle.
     */
    private function getContacts(int $userId, string $role): array
    {
        return match ($role) {
            'etudiant' => $this->db->query(
                'SELECT u.id, u.nom, u.prenom, u.role, u.avatar FROM utilisateurs u
                 WHERE u.id != ? AND (
                     (u.role = ? AND u.promotion_id = (SELECT promotion_id FROM utilisateurs WHERE id = ?))
                     OR u.role IN (\'enseignant\', \'assistant\')
                 ) ORDER BY u.nom',
                [$userId, 'etudiant', $userId]
            )->fetchAll(),

            'enseignant', 'assistant' => $this->db->query(
                "SELECT u.id, u.nom, u.prenom, u.role, u.avatar FROM utilisateurs u
                 WHERE u.id != ? AND u.role IN ('enseignant', 'assistant')
                 ORDER BY u.nom",
                [$userId]
            )->fetchAll(),

            'doyen' => $this->db->query(
                "SELECT u.id, u.nom, u.prenom, u.role, u.avatar FROM utilisateurs u
                 WHERE u.id != ? AND u.role = 'viceDoyen'",
                [$userId]
            )->fetchAll(),

            'viceDoyen' => $this->db->query(
                "SELECT u.id, u.nom, u.prenom, u.role, u.avatar FROM utilisateurs u
                 WHERE u.id != ? AND u.role = 'doyen'",
                [$userId]
            )->fetchAll(),

            default => [],
        };
    }

    /**
     * Traite un fichier uploadé et retourne l'ID du fichier en base.
     */
    private function traiterFichier(int $userId): ?int
    {
        try {
            $tous = array_merge(
                ALLOWED_TYPES['image'],
                ALLOWED_TYPES['video'],
                ALLOWED_TYPES['audio'],
                ALLOWED_TYPES['document']
            );
            Fichier::valider($_FILES['fichier'], $tous);

            $fichier = Fichier::creer($_FILES['fichier'], $userId);
            $fichier->traiter($_FILES['fichier']['tmp_name']);
            return $fichier->enregistrer();
        } catch (Exception $e) {
            return null;
        }
    }
}
