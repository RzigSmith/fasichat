<?php
/**
 * Classe SessionManager
 * Gère les sessions PHP de manière sécurisée.
 * Concept POO : encapsulation de la gestion de session.
 */
class SessionManager
{
    /**
     * Connecte un utilisateur en session.
     */
    public static function connecter(array $userData): void
    {
        // Regénérer l'ID de session pour prévenir la fixation de session
        session_regenerate_id(true);

        $_SESSION['user_id']    = (int)    $userData['id'];
        $_SESSION['user_role']  = $userData['role'];
        $_SESSION['user_nom']   = $userData['nom'];
        $_SESSION['user_prenom']= $userData['prenom'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_promo'] = $userData['promotion_id'] ?? null;
        $_SESSION['user_avatar']= $userData['avatar'] ?? '';
        $_SESSION['connecte_le']= time();
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     */
    public static function deconnecter(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Vérifie si un utilisateur est connecté.
     */
    public static function estConnecte(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Retourne l'ID de l'utilisateur connecté.
     */
    public static function getUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * Retourne le rôle de l'utilisateur connecté.
     */
    public static function getUserRole(): string
    {
        return $_SESSION['user_role'] ?? '';
    }

    /**
     * Retourne toutes les données de session de l'utilisateur.
     */
    public static function getUser(): array
    {
        return [
            'id'         => self::getUserId(),
            'role'       => self::getUserRole(),
            'nom'        => $_SESSION['user_nom'] ?? '',
            'prenom'     => $_SESSION['user_prenom'] ?? '',
            'email'      => $_SESSION['user_email'] ?? '',
            'promotion_id' => $_SESSION['user_promo'] ?? null,
            'avatar'     => $_SESSION['user_avatar'] ?? '',
        ];
    }

    /**
     * Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
     */
    public static function exigerConnexion(): void
    {
        if (!self::estConnecte()) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    /**
     * Vérifie que l'utilisateur connecté possède l'un des rôles autorisés.
     */
    public static function exigerRole(array $rolesAutorises): void
    {
        self::exigerConnexion();
        if (!in_array(self::getUserRole(), $rolesAutorises, true)) {
            http_response_code(403);
            include __DIR__ . '/../views/erreur.php';
            exit;
        }
    }

    /**
     * Stocke un message flash en session.
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Récupère et supprime le message flash.
     */
    public static function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
