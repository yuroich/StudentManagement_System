<?php
// DASHBOARD - admin sees Update/Delete; normal users can only view, search and add.
require_once __DIR__ . '/includes/init.php';
require_login();

$q      = trim($_GET['q'] ?? '');
$course = $_GET['course'] ?? '';
if (!in_array($course, COURSES, true)) { $course = ''; }

// Build the query with placeholders only (no user input concatenated)
$sql    = 'SELECT id, full_name, course, year_level, block, status FROM students WHERE 1=1';
$types  = '';
$params = [];
if ($q !== '') {
    $sql .= ' AND full_name LIKE ?';
    $types .= 's';
    $params[] = '%' . $q . '%';
}
if ($course !== '') {
    $sql .= ' AND course = ?';
    $types .= 's';
    $params[] = $course;
}
$sql .= ' ORDER BY id ASC';

$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalUsers    = (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$totalStudents = (int)$conn->query('SELECT COUNT(*) FROM students')->fetch_row()[0];

$admin = is_admin();
$pageTitle = ($admin ? 'Admin' : 'User') . ' Dashboard | Student Management System';
require __DIR__ . '/includes/header.php';
?>
<main class="container">
  <section class="stats">
    <div class="stat"><span>Total Users</span><strong><?= $totalUsers ?></strong></div>
    <div class="stat"><span>Total Students</span><strong><?= $totalStudents ?></strong></div>
  </section>

  <form class="toolbar" method="get" action="dashboard.php" autocomplete="off">
    <div class="search-wrap">
      <input type="text" id="searchInput" name="q" placeholder="Search..." value="<?= e($q) ?>">
      <div id="suggestions" class="suggestions" hidden></div>
    </div>
    <button type="submit" class="btn btn-orange btn-sm">Search</button>
    <span class="spacer"></span>
    <select name="course" onchange="this.form.submit()">
      <option value="">All Courses</option>
      <?php foreach (COURSES as $c): ?>
        <option value="<?= e($c) ?>" <?= $c === $course ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn btn-blue" id="addBtn">Add Student</button>
  </form>

  <table class="data-table">
    <thead>
      <tr>
        <th>Student Name</th>
        <th>Course, Yr &amp; Block</th>
        <?= $admin ? '<th>Action</th>' : '<th>Status</th>' ?>
        <th>Details</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$students): ?>
      <tr><td colspan="4" class="empty">
        <?= ($q !== '' || $course !== '') ? 'No results found. Try a different name or course.' : 'No students yet. Click Add Student to create the first record.' ?>
      </td></tr>
    <?php endif; ?>
    <?php foreach ($students as $s): ?>
      <tr>
        <td><?= e($s['full_name']) ?></td>
        <td><?= e($s['course'] . ' ' . $s['year_level'] . $s['block']) ?></td>
        <?php if ($admin): ?>
          <td class="actions">
            <button type="button" class="btn btn-blue btn-xs" data-edit="<?= (int)$s['id'] ?>">Update</button>
            <form method="post" action="actions/delete_student.php" class="inline" data-confirm="Delete <?= e($s['full_name']) ?>? This cannot be undone.">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button type="submit" class="btn btn-orange btn-xs">Delete</button>
            </form>
          </td>
        <?php else: ?>
          <td><?= e($s['status']) ?></td>
        <?php endif; ?>
        <td><button type="button" class="link-btn" data-view="<?= (int)$s['id'] ?>">View</button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>

<!-- VIEW modal -->
<div class="modal" id="viewModal" hidden>
  <div class="modal-box">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <h2>Student Profile</h2>
    <dl class="profile" id="profileList"></dl>
  </div>
</div>

<!-- ADD / UPDATE modal (one form, reused) -->
<div class="modal" id="formModal" hidden>
  <div class="modal-box">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <h2 id="formTitle">Add New Student</h2>
    <form method="post" id="studentForm" action="actions/add_student.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="f_id">

      <label for="f_name">Student Name:</label>
      <input type="text" name="full_name" id="f_name" maxlength="120" required>

      <label for="f_contact">Contact Number:</label>
      <input type="text" name="contact" id="f_contact" placeholder="Leave blank if none">

      <label for="f_gender">Gender:</label>
      <select name="gender" id="f_gender" required>
        <option>Male</option><option>Female</option>
      </select>

      <label for="f_address">Address:</label>
      <input type="text" name="address" id="f_address" required>

      <label for="f_birth">Birth Date:</label>
      <input type="date" name="birth_date" id="f_birth" max="<?= date('Y-m-d') ?>" required>

      <label for="f_course">Course:</label>
      <select name="course" id="f_course">
        <?php foreach (COURSES as $c): ?><option><?= e($c) ?></option><?php endforeach; ?>
      </select>

      <label for="f_year">Year:</label>
      <select name="year_level" id="f_year"><option>1</option><option>2</option><option>3</option><option>4</option></select>

      <label for="f_block">Block:</label>
      <select name="block" id="f_block"><option>A</option><option>B</option><option>C</option><option>D</option></select>

      <label for="f_status">Status:</label>
      <select name="status" id="f_status">
        <?php foreach (STATUSES as $st): ?><option><?= e($st) ?></option><?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-orange btn-block" id="formSubmit">ADD STUDENT</button>
    </form>
  </div>
</div>

<script>window.IS_ADMIN = <?= $admin ? 'true' : 'false' ?>;</script>
<script src="assets/js/app.js"></script>
</body>
</html>
