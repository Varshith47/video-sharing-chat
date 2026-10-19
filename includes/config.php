<?php
// VideoShare configuration. Set environment variables on hosted deployments.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $secureCookie,
        'samesite' => 'Lax'
    ]);
    session_start();
}

mysqli_report(MYSQLI_REPORT_OFF);
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'vsp');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    error_log('VideoShare database connection failed: ' . $conn->connect_error);
    exit('Database connection failed. Check the database settings in includes/config.php or your hosting environment variables.');
}
$conn->set_charset('utf8mb4');

define('APP_ROOT', dirname(__DIR__));
define('VIDEO_UPLOAD_PATH', 'uploads/videos/');
define('THUMBNAIL_UPLOAD_PATH', 'uploads/thumbnails/');
foreach ([APP_ROOT . '/uploads/videos', APP_ROOT . '/uploads/thumbnails'] as $directory) {
    if (!is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfToken(): string { return $_SESSION['csrf_token'] ?? ''; }
function verifyCsrfToken($token): bool {
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        exit('Please log in to use chat.');
    }
}
?>