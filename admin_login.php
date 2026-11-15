<?php
// ADMIN LOGIN PAGE - separate sign-in for the (single) administrator account.
// The login rules (lockout, one admin session, prepared statements) live in attempt_login() in includes/functions.php.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$error = '';
$flash = get_flash();
if ($flash) { $error = $flash['msg']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = attempt_login('admin');    // redirects to the dashboard when it succeeds
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | Student Management System</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="auth-body">
  <?php include __DIR__ . '/includes/auth_bg.php'; ?>
  <main class="auth-card">
    <h1>ADMIN LOGIN</h1>
    <p class="auth-note">Authorized administrator only</p>
    <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="text" name="username" placeholder="Admin username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn btn-orange btn-block">LOGIN AS ADMIN</button>
    </form>
    <p class="auth-switch"><a href="index.php">&larr; Back to user sign in</a></p>
  </main>
</body>
</html>
