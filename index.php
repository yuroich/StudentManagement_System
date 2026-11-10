<?php
// LOGIN PAGE - verifies credentials, locks after repeated failures, allows one admin session only.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$error = '';
$flash = get_flash();
if ($flash) { $error = $flash['msg']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        // Prepared statement => no SQL injection
        $stmt = $conn->prepare(
            'SELECT id, full_name, password, role, active_session, failed_attempts,
                    TIMESTAMPDIFF(SECOND, NOW(), locked_until)  AS lock_left,
                    TIMESTAMPDIFF(SECOND, last_activity, NOW()) AS idle_seconds
             FROM users WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $error = 'Invalid username or password.';
            log_error("Failed login: unknown username '$username' from " . ($_SERVER['REMOTE_ADDR'] ?? '?'));
        } elseif ($user['lock_left'] !== null && (int)$user['lock_left'] > 0) {
            $mins = (int)ceil($user['lock_left'] / 60);
            $error = "Too many failed attempts. Try again in $mins minute(s).";
        } elseif (!password_verify($password, $user['password'])) {
            $attempts = (int)$user['failed_attempts'] + 1;
            if ($attempts >= MAX_FAILED_ATTEMPTS) {
                $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?');
                $mins = LOCK_MINUTES;
                $stmt->bind_param('ii', $mins, $user['id']);
                $stmt->execute();
                $error = 'Too many failed attempts. This account is locked for ' . LOCK_MINUTES . ' minutes.';
            } else {
                $stmt = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
                $stmt->bind_param('ii', $attempts, $user['id']);
                $stmt->execute();
                $left = MAX_FAILED_ATTEMPTS - $attempts;
                $error = "Invalid username or password. $left attempt(s) left.";
            }
            log_error("Failed login for '$username' (attempt $attempts)");
        } elseif ($user['role'] === 'admin'
                  && !empty($user['active_session'])
                  && $user['active_session'] !== session_id()
                  && $user['idle_seconds'] !== null
                  && (int)$user['idle_seconds'] < ADMIN_IDLE_SECONDS) {
            // Only ONE admin may be logged in at a time
            $error = 'The admin account is already logged in on another device. Only one admin session is allowed.';
            log_error('Blocked second admin login attempt from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
        } else {
            session_regenerate_id(true);          // prevent session fixation
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role']    = $user['role'];

            $sid = session_id();
            $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL, active_session = ?, last_activity = NOW() WHERE id = ?');
            $stmt->bind_param('si', $sid, $user['id']);
            $stmt->execute();
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in | Student Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
  <?php include __DIR__ . '/includes/auth_bg.php'; ?>
  <main class="auth-card">
    <h1>SIGN IN</h1>
    <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="text" name="username" placeholder="Username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn btn-orange btn-block">LOGIN</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="register.php">Sign up now</a></p>
  </main>
</body>
</html>