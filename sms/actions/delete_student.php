<?php
// DELETE STUDENT - ADMIN ONLY.
require_once dirname(__DIR__) . '/includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../dashboard.php'); }
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

flash($stmt->affected_rows ? 'success' : 'error', $stmt->affected_rows ? 'Student deleted.' : 'Student not found.');
redirect('../dashboard.php');
