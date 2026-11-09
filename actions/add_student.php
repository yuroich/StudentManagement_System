<?php
// ADD STUDENT - allowed for admin AND normal users.
require_once dirname(__DIR__) . '/includes/init.php';
require_login('../index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../dashboard.php'); }
csrf_check();

[$errors, $s] = validate_student($_POST);
if ($errors) {
    flash('error', implode(' ', $errors));
    redirect('../dashboard.php');
}

$stmt = $conn->prepare(
    'INSERT INTO students (full_name, contact, gender, address, birth_date, course, year_level, block, status, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssssissi',
    $s['full_name'], $s['contact'], $s['gender'], $s['address'], $s['birth_date'],
    $s['course'], $s['year_level'], $s['block'], $s['status'], $_SESSION['user_id']
);
$stmt->execute();

flash('success', 'Student added.');
redirect('../dashboard.php');
