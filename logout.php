<?php
// LOGOUT - ends the session and frees the single admin slot.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    $stmt = $conn->prepare('UPDATE users SET active_session = NULL WHERE id = ? AND active_session = ?');
    $sid = session_id();
    $stmt->bind_param('is', $_SESSION['user_id'], $sid);
    $stmt->execute();
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
