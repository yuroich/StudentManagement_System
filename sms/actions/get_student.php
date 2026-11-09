<?php
// Returns one student as JSON (used by the View and Update pop-ups).
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');

if (!is_logged_in()) { http_response_code(401); echo json_encode(['error' => 'Not logged in']); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT id, full_name, contact, gender, address, birth_date, course, year_level, block, status FROM students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) { http_response_code(404); echo json_encode(['error' => 'Student not found']); exit; }
echo json_encode($row);
