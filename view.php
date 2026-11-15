<?php
// STUDENT DETAILS PAGE - read-only profile of one student, opened from the "View" link on the dashboard.
require_once __DIR__ . '/includes/init.php';
require_login();

$id     = (int)($_GET['id'] ?? 0);
$q      = trim($_GET['q'] ?? '');
$course = $_GET['course'] ?? '';
if (!in_array($course, COURSES, true)) { $course = ''; }

// "Back" returns to the same search / course filter the user came from
$backParams = array_filter(['q' => $q, 'course' => $course], fn($v) => $v !== '');
$backUrl    = 'dashboard.php' . ($backParams ? '?' . http_build_query($backParams) : '');

// Prepared statement => no SQL injection
$stmt = $conn->prepare(
    'SELECT id, full_name, contact, gender, address, birth_date, course, year_level, block, status, created_at
     FROM students WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();

if (!$s) {
    flash('error', 'That student record was not found.');
    redirect($backUrl);
}

$age       = (new DateTime($s['birth_date']))->diff(new DateTime('today'))->y;
$birthText = date('F j, Y', strtotime($s['birth_date']));
$addedText = date('F j, Y', strtotime($s['created_at']));
$pillClass = preg_replace('/[^a-z]/', '', strtolower($s['status']));   // regular / irregular / graduate

$pageTitle = $s['full_name'] . ' | Student Details';
require __DIR__ . '/includes/header.php';
?>
<main class="container">
  <a class="back-link" href="<?= e($backUrl) ?>">&larr; Back to student list</a>

  <article class="detail-card">
    <div class="detail-head">
      <div class="detail-avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($s['full_name'], 0, 1))) ?></div>
      <div class="detail-title">
        <h1><?= e($s['full_name']) ?></h1>
        <p><?= e($s['course']) ?> &middot; Year <?= (int)$s['year_level'] ?> &middot; Block <?= e($s['block']) ?></p>
      </div>
      <span class="pill pill-<?= e($pillClass) ?>"><?= e($s['status']) ?></span>
    </div>

    <dl class="detail-grid">
      <div class="detail-item"><dt>Course</dt><dd><?= e($s['course']) ?></dd></div>
      <div class="detail-item"><dt>Year &amp; Block</dt><dd><?= (int)$s['year_level'] ?><?= e($s['block']) ?></dd></div>
      <div class="detail-item"><dt>Gender</dt><dd><?= e($s['gender']) ?></dd></div>
      <div class="detail-item"><dt>Birth Date</dt><dd><?= e($birthText) ?> (<?= (int)$age ?> yrs old)</dd></div>
      <div class="detail-item"><dt>Contact Number</dt><dd><?= e($s['contact']) ?></dd></div>
      <div class="detail-item"><dt>Status</dt><dd><?= e($s['status']) ?></dd></div>
      <div class="detail-item detail-wide"><dt>Address</dt><dd><?= e($s['address']) ?></dd></div>
      <div class="detail-item detail-wide"><dt>Record added</dt><dd><?= e($addedText) ?></dd></div>
    </dl>

    <div class="detail-actions">
      <a class="btn btn-orange" href="<?= e($backUrl) ?>">Back to list</a>
    </div>
  </article>
</main>
<script src="assets/js/menu.js?v=<?= filemtime(__DIR__ . '/assets/js/menu.js') ?>"></script>
</body>
</html>
