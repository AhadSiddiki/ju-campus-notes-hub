<?php
require_once '../config/init.php';
require_once '../includes/google-oauth.php';

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Google authentication failed. No authorization code received.';
    header('Location: login.php');
    exit();
}

$code = $_GET['code'];

// Exchange code for access token
$access_token = getGoogleAccessToken($code);
if (!$access_token) {
    $_SESSION['error'] = 'Failed to get access token from Google.';
    header('Location: login.php');
    exit();
}

// Get user info from Google
$user_info = getGoogleUserInfo($access_token);
if (!$user_info) {
    $_SESSION['error'] = 'Failed to get user information from Google.';
    header('Location: login.php');
    exit();
}

// Verify email domain
if (!verifyJunivEmail($user_info['email'])) {
    $_SESSION['error'] = 'Only @juniv.edu email addresses are allowed.';
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Check if user already exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_info['email']);
$stmt->execute();
$existing_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing_user) {
    // User exists - update OAuth info and log them in
    $stmt = $conn->prepare("UPDATE users SET google_id = ?, oauth_provider = 'google', email_verified_by_oauth = TRUE, last_login = NOW() WHERE user_id = ?");
    $stmt->bind_param("si", $user_info['id'], $existing_user['user_id']);
    $stmt->execute();
    $stmt->close();
    
    // Set session
    $_SESSION['user_id'] = $existing_user['user_id'];
    $_SESSION['user_name'] = $existing_user['full_name'];
    $_SESSION['user_email'] = $existing_user['email'];
    
    closeDBConnection($conn);
    header('Location: ../dashboard/user-dashboard.php');
    exit();
} else {
    // New user - store their info and redirect to complete registration
    $_SESSION['google_user'] = [
        'google_id' => $user_info['id'],
        'email' => $user_info['email'],
        'name' => $user_info['name'] ?? '',
        'picture' => $user_info['picture'] ?? ''
    ];
    
    closeDBConnection($conn);
    header('Location: complete-registration.php');
    exit();
}
?>
