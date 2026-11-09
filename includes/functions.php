<?php
// ---------- Constants ----------
const COURSES  = ['BSIT', 'BSCS', 'BSIS'];
const STATUSES = ['Regular', 'Irregular', 'Graduate'];
const MAX_FAILED_ATTEMPTS = 5;      // wrong passwords before temporary lock
const LOCK_MINUTES        = 5;
const ADMIN_IDLE_SECONDS  = 900;    // admin session considered abandoned after 15 min

// ---------- Error logging ----------
function log_error(string $message): void
{
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    @file_put_contents($dir . '/error.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

// Log anything unexpected instead of showing technical details to the user.
set_exception_handler(function (Throwable $e) {
    log_error(get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    exit('Something went wrong. The error has been logged.');
});

// ---------- Output & security helpers ----------
function e(?string $value): string          // escape output (prevents XSS)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Invalid or expired form. Please go back and try again.');
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

function get_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------- Authentication / authorisation ----------
function is_logged_in(): bool { return !empty($_SESSION['user_id']); }
function is_admin(): bool     { return ($_SESSION['role'] ?? '') === 'admin'; }

function require_login(string $loginPath = 'index.php'): void
{
    global $conn;
    if (!is_logged_in()) { redirect($loginPath); }

    // Single-admin rule: if another browser logged in as admin, this session is no longer valid.
    if (is_admin()) {
        $stmt = $conn->prepare('SELECT active_session FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row || $row['active_session'] !== session_id()) {
            session_unset();
            session_destroy();
            session_start();
            flash('error', 'Your admin session ended because the account was used somewhere else.');
            redirect($loginPath);
        }
        $stmt = $conn->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
    }
}

// For files inside /actions (paths go one level up)
function require_admin(): void
{
    require_login('../index.php');
    if (!is_admin()) {
        http_response_code(403);
        flash('error', 'Only the admin can do that.');
        redirect('../dashboard.php');
    }
}

function current_user(): array
{
    global $conn;
    static $user = null;
    if ($user === null) {
        $stmt = $conn->prepare('SELECT id, full_name, email, username, role, avatar FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc() ?: [];
    }
    return $user;
}

function avatar_html(array $u, string $class = 'avatar'): string
{
    if (!empty($u['avatar']) && is_file(dirname(__DIR__) . '/assets/uploads/' . $u['avatar'])) {
        return '<img class="' . $class . '" src="assets/uploads/' . e($u['avatar']) . '" alt="Profile">';
    }
    $initial = strtoupper(substr($u['full_name'] ?? '?', 0, 1));
    return '<span class="' . $class . ' avatar-fallback">' . e($initial) . '</span>';
}

// ---------- Student validation ----------
// Returns [array $errors, array $clean]
function validate_student(array $in): array
{
    $errors = [];
    $clean = [];

    $clean['full_name'] = trim($in['full_name'] ?? '');
    if ($clean['full_name'] === '' || mb_strlen($clean['full_name']) > 120) { $errors[] = 'Student name is required (max 120 characters).'; }

    $contact = trim($in['contact'] ?? '');
    $clean['contact'] = $contact === '' ? 'N/A' : $contact;
    if ($clean['contact'] !== 'N/A' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $clean['contact'])) { $errors[] = 'Contact number is not valid.'; }

    $clean['gender'] = $in['gender'] ?? '';
    if (!in_array($clean['gender'], ['Male', 'Female'], true)) { $errors[] = 'Choose a gender.'; }

    $clean['address'] = trim($in['address'] ?? '');
    if ($clean['address'] === '') { $errors[] = 'Address is required.'; }

    $clean['birth_date'] = $in['birth_date'] ?? '';
    $d = DateTime::createFromFormat('Y-m-d', $clean['birth_date']);
    if (!$d || $d->format('Y-m-d') !== $clean['birth_date'] || $d > new DateTime('today')) { $errors[] = 'Enter a valid birth date.'; }

    $clean['course'] = $in['course'] ?? '';
    if (!in_array($clean['course'], COURSES, true)) { $errors[] = 'Choose a valid course.'; }

    $clean['year_level'] = (int)($in['year_level'] ?? 0);
    if ($clean['year_level'] < 1 || $clean['year_level'] > 4) { $errors[] = 'Year must be 1 to 4.'; }

    $clean['block'] = strtoupper($in['block'] ?? '');
    if (!preg_match('/^[A-D]$/', $clean['block'])) { $errors[] = 'Block must be A to D.'; }

    $clean['status'] = $in['status'] ?? '';
    if (!in_array($clean['status'], STATUSES, true)) { $errors[] = 'Choose a valid status.'; }

    return [$errors, $clean];
}
