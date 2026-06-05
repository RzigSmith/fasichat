<?php
/**
 * Configuration générale de FasiChat Classroom
 */

define('DB_PATH', __DIR__ . '/../database/fasichat.db');

// SQLite configuration
define('DB_DRIVER', 'sqlite');

// MySQL connection (kept for reference, not used)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'fasichat_classroom');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base path detection (useful when the app is in a subdirectory, e.g., /fasichat)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_replace('\\','/', dirname($scriptName)), '/');
if ($basePath === '' || $basePath === '/') {
    $basePath = '';
}
define('BASE_PATH', $basePath);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 Mo
define('ALLOWED_TYPES', [
    'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'video' => ['video/mp4', 'video/webm', 'video/ogg'],
    'audio' => ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/webm'],
    'document' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain'
    ],
]);

define('ROLES', [
    'etudiant'   => 'Etudiant',
    'enseignant' => 'Enseignant',
    'assistant'  => 'Assistant',
    'doyen'      => 'Doyen',
    'viceDoyen'  => 'Vice-Doyen',
    'apparitaire'=> 'Apparitaire',
]);

// Autoloader simple pour les classes
spl_autoload_register(function (string $class) {
    $dirs = [
        __DIR__ . '/../classes/',
        __DIR__ . '/../controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
