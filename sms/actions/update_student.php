<?php
// UPDATE STUDENT - ADMIN ONLY (enforced on the server, not just hidden in the UI).
require_once dirname(__DIR__) . '/includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../dashboard.php'); }
csrf_check();

$id = (int)($_POST['id'] ?? 0);
[$errors, $s] = validate_student($_POST);
if ($id <= 0) { $errors[] = 'Invalid student.'; }
if ($errors) {
    flash('error', implode(' ', $errors));
    redirect('../dashboard.php');
}

$stmt = $conn->prepare(
    'UPDATE students SET full_name=?, contact=?, gender=?, address=?, birth_date=?, course=?, year_level=?, block=?, status=? WHERE id=?'
);
$stmt->bind_param('ssssssissi',
    $s['full_name'], $s['contact'], $s['gender'], $s['address'], $s['birth_date'],
    $s['course'], $s['year_level'], $s['block'], $s['status'], $id
);
$stmt->execute();

flash('success', 'Student updated.');
redirect('../dashboard.php');
