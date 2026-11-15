<?php
// USER SIGN-IN PAGE - regular accounts only. The administrator signs in on admin_login.php.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$error = '';
$flash = get_flash();
if ($flash) { $error = $flash['msg']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = attempt_login('user');     // redirects to the dashboard when it succeeds
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in | Student Management System</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
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
    <p class="auth-switch auth-switch-sub">Administrator? <a href="admin_login.php">Admin Login</a></p>
  </main>
</body>
</html>