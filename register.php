<?php
// SIGN-UP PAGE - creates a normal "user" account. Admin accounts can never be created here.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$errors = [];
$old = ['full_name' => '', 'email' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['username']  = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';

    if ($old['full_name'] === '' || mb_strlen($old['full_name']) > 100) { $errors[] = 'Full name is required.'; }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))              { $errors[] = 'Enter a valid email address.'; }
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $old['username']))       { $errors[] = 'Username: 3-30 letters, numbers, dot or underscore.'; }
    if (strlen($password) < 6)                                          { $errors[] = 'Password must be at least 6 characters.'; }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $old['username'], $old['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'That username or email is already registered.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);   // password encryption (bcrypt)
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param('ssss', $old['full_name'], $old['email'], $old['username'], $hash);
        $stmt->execute();
        flash('success', 'Account created. You can now log in.');
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign up | Student Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
  <?php include __DIR__ . '/includes/auth_bg.php'; ?>
  <main class="auth-card">
    <h1>Sign Up</h1>
    <?php foreach ($errors as $err): ?><p class="form-error" role="alert"><?= e($err) ?></p><?php endforeach; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="text"     name="full_name" placeholder="Full Name" value="<?= e($old['full_name']) ?>" required>
      <input type="email"    name="email"     placeholder="Email"     value="<?= e($old['email']) ?>" required>
      <input type="text"     name="username"  placeholder="Username"  value="<?= e($old['username']) ?>" required>
      <input type="password" name="password"  placeholder="Password (min. 6 characters)" required>
      <button type="submit" class="btn btn-orange btn-block">Register</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="index.php">Login</a></p>
  </main>
</body>
</html>