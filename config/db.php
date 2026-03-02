<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habit_tracker');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone setting
date_default_timezone_set('Asia/Kolkata');

// Helper function to sanitize input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN';
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Redirect if not admin
function require_admin() {
    if (!is_admin()) {
        header("Location: ../index.php");
        exit();
    }
}
?>