<?php
// MANAGE ACCOUNT - any logged-in user edits their OWN name, email, username, password, photo.
require_once __DIR__ . '/includes/init.php';
require_login();

$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($full_name === '' || mb_strlen($full_name) > 100)         { $errors[] = 'Full name is required.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))               { $errors[] = 'Enter a valid email address.'; }
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username))        { $errors[] = 'Username: 3-30 letters, numbers, dot or underscore.'; }
    if ($password !== '' && strlen($password) < 4)                { $errors[] = 'Password must be at least 4 characters.'; }

    // Username / email must not belong to someone else
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?');
        $stmt->bind_param('ssi', $username, $email, $me['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) { $errors[] = 'That username or email is already used by another account.'; }
    }

    // Optional profile photo (jpg/png/webp, max 2 MB, type checked from file content)
    $avatarName = $me['avatar'];
    if (!$errors && !empty($_FILES['avatar']['name'])) {
        $f = $_FILES['avatar'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
        if ($f['error'] !== UPLOAD_ERR_OK)       { $errors[] = 'Photo upload failed. Try again.'; }
        elseif ($f['size'] > 2 * 1024 * 1024)    { $errors[] = 'Photo must be 2 MB or smaller.'; }
        elseif (!isset($allowed[$mime]))         { $errors[] = 'Photo must be a JPG, PNG or WEBP image.'; }
        else {
            $avatarName = 'user_' . $me['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/assets/uploads/' . $avatarName)) {
                log_error('Could not save upload to assets/uploads');
                $errors[] = 'Could not save the photo.';
            }
        }
    }

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET full_name=?, email=?, username=?, password=?, avatar=? WHERE id=?');
            $stmt->bind_param('sssssi', $full_name, $email, $username, $hash, $avatarName, $me['id']);
        } else {
            $stmt = $conn->prepare('UPDATE users SET full_name=?, email=?, username=?, avatar=? WHERE id=?');
            $stmt->bind_param('ssssi', $full_name, $email, $username, $avatarName, $me['id']);
        }
        $stmt->execute();
        flash('success', 'Account updated.');
        redirect('dashboard.php');
    }
    // keep what the user typed if there were errors
    $me = array_merge($me, ['full_name' => $full_name, 'email' => $email, 'username' => $username]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Account | Student Management System</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="app-body account-body">
  <main class="account-card">
    <h1>Manage Account</h1>
    <?php foreach ($errors as $err): ?><p class="form-error light" role="alert"><?= e($err) ?></p><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="avatar-edit">
        <?= avatar_html($me, 'avatar avatar-lg') ?>
        <label class="btn btn-orange btn-sm" for="avatarInput">Change Profile Image</label>
        <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
        <small id="avatarName"></small>
      </div>

      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" value="<?= e($me['full_name']) ?>" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($me['email']) ?>" required>

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= e($me['username']) ?>" required>

      <label for="password">Password</label>
      <div class="pw-wrap">
        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" autocomplete="new-password">
        <button type="button" class="pw-toggle" id="pwToggle">show</button>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn btn-navy">Save Changes</button>
        <a href="dashboard.php" class="btn btn-orange">Cancel</a>
      </div>
    </form>
  </main>
  <script src="assets/js/account.js"></script>
</body>
</html>
