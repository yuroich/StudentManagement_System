<?php
// Loaded at the top of every page: session, helpers, database.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/db.php';
