<?php
/**
 * FasiChat Classroom — Routeur principal
 * Point d'entrée de l'application. Charge la config et dispatche les routes.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// Initialiser la base de données si elle n'existe pas encore
if (!file_exists(DB_PATH)) {
    $db = BaseDeDonnees::getInstance();
    $db->initialiser();

    // Seeder les données de démonstration
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    // Exécuter ligne par ligne pour SQLite
    $stmts = array_filter(
        array_map('trim', explode(';', $seed)),
        fn($s) => !empty($s) && !str_starts_with(ltrim($s), '--')
    );
    foreach ($stmts as $stmt) {
        try {
            $db->getPDO()->exec($stmt);
        } catch (PDOException $e) {
            // Ignorer les erreurs de seed (doublons, etc.)
        }
    }
}

// Servir les fichiers uploadés
$uri = $_SERVER['REQUEST_URI'];

if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($uri, BASE_PATH . '/uploads/')) {
    $rel = substr(parse_url($uri, PHP_URL_PATH), strlen(BASE_PATH));
    $file = __DIR__ . $rel;
    if (file_exists($file) && is_file($file)) {
        $mime = mime_content_type($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Servir les fichiers uploadés (root install)
if (str_starts_with($uri, '/uploads/')) {
    $file = __DIR__ . '/uploads/' . basename(parse_url($uri, PHP_URL_PATH));
    if (file_exists($file) && is_file($file)) {
        $mime = mime_content_type($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Servir les fichiers CSS/JS statiques
if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($uri, BASE_PATH . '/public/')) {
    $rel = substr(parse_url($uri, PHP_URL_PATH), strlen(BASE_PATH));
    $file = __DIR__ . $rel;
    if (file_exists($file) && is_file($file)) {
        $mime = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'svg'  => 'image/svg+xml',
            default => mime_content_type($file) ?: 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Servir les fichiers CSS/JS statiques (root install)
if (str_starts_with($uri, '/public/')) {
    $file = __DIR__ . parse_url($uri, PHP_URL_PATH);
    if (file_exists($file) && is_file($file)) {
        $mime = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'svg'  => 'image/svg+xml',
            default => mime_content_type($file) ?: 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Router HTTP
$path   = parse_url($uri, PHP_URL_PATH);
// Remove base path if app is installed in a subdirectory
if (defined('BASE_PATH') && BASE_PATH !== '' && str_starts_with($path, BASE_PATH)) {
    $path = substr($path, strlen(BASE_PATH));
}
$path   = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Définition des routes
$routes = [
    'GET'  => [
        '/'                   => fn() => (new AuthController())->login(),
        '/login'              => fn() => (new AuthController())->login(),
        '/logout'             => fn() => (new AuthController())->logout(),
        '/chat'               => fn() => (new MessageController())->index(),
        '/chat/messages'      => fn() => (new MessageController())->nouveauxMessages(),
        '/valve'              => fn() => (new ValveController())->index(),
        '/valve/publier'      => fn() => (new ValveController())->publier(),
        '/valve/modifier'     => fn() => (new ValveController())->modifier(),
        '/convocations'       => fn() => (new ConvocationController())->index(),
        '/convocation/envoyer'=> fn() => (new ConvocationController())->envoyer(),
        '/dashboard'          => fn() => (new DashboardController())->enseignant(),
        '/admin'              => fn() => (new DashboardController())->admin(),
        '/register'           => fn() => (new AuthController())->register(),
        '/mur/publications'   => fn() => (new MurController())->getPublications(),
    ],
    'POST' => [
        '/login'              => fn() => (new AuthController())->login(),
        '/logout'             => fn() => (new AuthController())->logout(),
        '/chat/envoyer-prive' => fn() => (new MessageController())->envoyerPrive(),
        '/chat/envoyer-public'=> fn() => (new MessageController())->envoyerPublic(),
        '/valve/publier'      => fn() => (new ValveController())->publier(),
        '/valve/modifier'     => fn() => (new ValveController())->modifier(),
        '/valve/supprimer'    => fn() => (new ValveController())->supprimer(),
        '/convocation/envoyer'=> fn() => (new ConvocationController())->envoyer(),
        '/convocation/lire'   => fn() => (new ConvocationController())->marquerLu(),
        '/register'           => fn() => (new AuthController())->register(),
        '/mur/publier'        => fn() => (new MurController())->publier(),
    ],
];

$handler = $routes[$method][$path] ?? null;

if ($handler) {
    $handler();
} else {
    http_response_code(404);
    echo '<h1>404 — Page introuvable</h1>';
}
