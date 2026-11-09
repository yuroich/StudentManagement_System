<?php
// Shared top bar. Expects $pageTitle (optional). Uses current_user().
$me = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Student Management System') ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
<header class="topbar">
  <div class="brand"><span>BU Polangui</span> Student Management System</div>
  <div class="profile-menu">
    <button type="button" class="avatar-btn" id="profileBtn" aria-label="Account menu"><?= avatar_html($me) ?></button>
    <div class="dropdown" id="profileDropdown">
      <strong><?= e($me['full_name']) ?></strong>
      <small><?= e(ucfirst($me['role'])) ?></small>
      <a href="manage_account.php">Manage Account</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </div>
</header>
<?php if ($flash): ?>
  <div class="toast <?= e($flash['type']) ?>" id="toast"><?= e($flash['msg']) ?></div>
<?php endif; ?>
