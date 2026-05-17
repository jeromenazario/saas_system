<?php
/**
 * logout.php – SaaS Manager
 * -----------------------------------------------------------------------
 * Properly destroys the session and clears the session cookie so the
 * browser cannot reuse the old session ID after logout.
 * -----------------------------------------------------------------------
 */
session_start();

// Remove all session variables
$_SESSION = [];

// Expire the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session on the server
session_destroy();

header('Location: login.php?logged_out=1');
exit;
?>
