<?php
/**
 * Contrôleur d'authentification
 * Gère la connexion, la déconnexion et l'inscription.
 */
class AuthController
{
    private BaseDeDonnees $db;

    public function __construct()
    {
        $this->db = BaseDeDonnees::getInstance();
    }

    /**
     * Affiche le formulaire de connexion (GET) ou traite la soumission (POST).
     */
    public function login(): void
    {
        if (SessionManager::estConnecte()) {
            header('Location: ' . BASE_PATH . '/chat');
            exit;
        }

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email      = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            if (!$email || !$motDePasse) {
                $erreur = 'Veuillez remplir tous les champs.';
            } else {
                $user = $this->db->query(
                    'SELECT * FROM utilisateurs WHERE email = ?',
                    [$email]
                )->fetch();

                if ($user && password_verify($motDePasse, $user['mot_de_passe'])) {
                    SessionManager::connecter($user);
                    header('Location: ' . BASE_PATH . '/chat');
                    exit;
                } else {
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }

        include __DIR__ . '/../views/login.php';
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(): void
    {
        SessionManager::deconnecter();
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }

    /**
     * Inscription d'un nouvel utilisateur (formulaire admin ou auto-inscription étudiant).
     */
    public function register(): void
    {
        SessionManager::exigerConnexion();
        $currentRole = SessionManager::getUserRole();

        // Seuls Doyen, Vice-Doyen peuvent créer des comptes
        if (!in_array($currentRole, ['doyen', 'viceDoyen', 'apparitaire'], true)) {
            http_response_code(403);
            include __DIR__ . '/../views/erreur.php';
            exit;
        }

        $erreur  = null;
        $succes  = null;
        $promos  = Promotion::toutes();
        $cours   = Cours::tous();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validerRegistration($_POST);

            if (isset($data['erreur'])) {
                $erreur = $data['erreur'];
            } else {
                $hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);

                try {
                    $this->db->query(
                        'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, promotion_id, date_inscription)
                         VALUES (?, ?, ?, ?, ?, ?, datetime(\'now\'))',
                        [$data['nom'], $data['prenom'], $data['email'], $hash, $data['role'], $data['promotion_id'] ?? null]
                    );

                    $newUserId = (int) $this->db->lastInsertId();

                    // Si c'est un enseignant/assistant, affecter aux cours sélectionnés
                    if (in_array($data['role'], ['enseignant', 'assistant'], true) && !empty($data['cours_ids'])) {
                        foreach ($data['cours_ids'] as $coursId) {
                            $this->db->query(
                                'INSERT OR IGNORE INTO cours_enseignants (cours_id, enseignant_id) VALUES (?, ?)',
                                [(int)$coursId, $newUserId]
                            );
                        }
                    }

                    $succes = 'Compte créé avec succès pour ' . htmlspecialchars($data['prenom'] . ' ' . $data['nom']);
                } catch (Exception $e) {
                    $erreur = 'Email déjà utilisé ou erreur lors de la création.';
                }
            }
        }

        include __DIR__ . '/../views/register.php';
    }

    private function validerRegistration(array $post): array
    {
        $nom        = trim($post['nom'] ?? '');
        $prenom     = trim($post['prenom'] ?? '');
        $email      = filter_var($post['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $mdp        = $post['mot_de_passe'] ?? '';
        $mdpConfirm = $post['mot_de_passe_confirm'] ?? '';
        $role       = $post['role'] ?? '';

        if (!$nom || !$prenom || !$email || !$mdp || !$role) {
            return ['erreur' => 'Tous les champs obligatoires doivent être remplis.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['erreur' => 'Email invalide.'];
        }
        if ($mdp !== $mdpConfirm) {
            return ['erreur' => 'Les mots de passe ne correspondent pas.'];
        }
        if (strlen($mdp) < 6) {
            return ['erreur' => 'Le mot de passe doit contenir au moins 6 caractères.'];
        }
        if (!array_key_exists($role, ROLES)) {
            return ['erreur' => 'Rôle invalide.'];
        }

        return [
            'nom'          => htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'),
            'prenom'       => htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8'),
            'email'        => $email,
            'mot_de_passe' => $mdp,
            'role'         => $role,
            'promotion_id' => !empty($post['promotion_id']) ? (int)$post['promotion_id'] : null,
            'cours_ids'    => $post['cours_ids'] ?? [],
        ];
    }
}
