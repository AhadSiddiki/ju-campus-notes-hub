<?php
// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Base URL configuration
define('BASE_URL', 'http://localhost/campus-notes-hub/');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'jpg', 'jpeg', 'png', 'zip']);

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function to validate juniv.edu email
function isJunivEmail($email) {
    return preg_match('/@juniv\.edu$/', $email);
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Helper function to get time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "just now";
    } elseif ($difference < 3600) {
        return floor($difference / 60) . " minutes ago";
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . " hours ago";
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . " days ago";
    } else {
        return date('M d, Y', $timestamp);
    }
}
?>
