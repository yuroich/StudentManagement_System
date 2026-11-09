<?php
// Database connection settings (XAMPP defaults). Change if yours differ.
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'student_management';

// Make mysqli throw exceptions so errors can be caught and logged.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    log_error('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Cannot connect to the database. Please make sure MySQL is running and database.sql was imported.');
}
