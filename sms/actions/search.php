<?php
// Live search suggestions: returns up to 6 matching student names as JSON.
// Names that START with the text are ranked before names that merely contain it.
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json');

if (!is_logged_in()) { http_response_code(401); echo json_encode([]); exit; }

$q = trim($_GET['q'] ?? '');
if ($q === '') { echo json_encode([]); exit; }

$like   = '%' . $q . '%';
$prefix = $q . '%';
$stmt = $conn->prepare(
    'SELECT id, full_name FROM students WHERE full_name LIKE ?
     ORDER BY (full_name LIKE ?) DESC, full_name ASC LIMIT 6'
);
$stmt->bind_param('ss', $like, $prefix);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
