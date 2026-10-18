<?php



if (session_status() == PHP_SESSION_NONE) {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $isIframeOrHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
        (strpos($host, 'e2b.app') !== false) ||
        (strpos($host, 'arena.ai') !== false)
    );

    $sessionName = 'PHPSESSID';
    session_name($sessionName);

    if (!isset($_COOKIE[$sessionName])) {
        if (!empty($_POST[$sessionName]) && preg_match('/^[a-zA-Z0-9,\-]{20,64}$/', $_POST[$sessionName])) {
            session_id($_POST[$sessionName]);
        } elseif (!empty($_GET[$sessionName]) && preg_match('/^[a-zA-Z0-9,\-]{20,64}$/', $_GET[$sessionName])) {
            session_id($_GET[$sessionName]);
        }
    }

    session_set_cookie_params(
        86400,
        '/',
        '',
        $isIframeOrHttps,
        true
    );

    ini_set('session.use_only_cookies', '0');

    session_start();

    if ($isIframeOrHttps && session_id()) {
        header('Set-Cookie: ' . session_name() . '=' . session_id() . '; Path=/; Secure; HttpOnly; SameSite=None', false);
    }
}



$db_host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
$db_user = getenv('DB_USER') ? getenv('DB_USER') : 'root';
$db_pass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ? getenv('DB_NAME') : 'student_portal';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);


if (!$conn) {
    $conn = mysqli_connect($db_host, 'root', '', $db_name);
}

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fee2e2;color:#991b1b;border-radius:8px;margin:30px;'>
        <h2 style='margin-top:0;'>Database Connection Error</h2>
        <p>Could not connect to the database <code>" . htmlspecialchars($db_name) . "</code> on host <code>" . htmlspecialchars($db_host) . "</code>.</p>
        <p><strong>MySQL Error:</strong> " . htmlspecialchars(mysqli_connect_error()) . "</p>
        <p>Please check your database credentials in <code>config/database.php</code>.</p>
    </div>");
}

mysqli_set_charset($conn, "utf8mb4");
