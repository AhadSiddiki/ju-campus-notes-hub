<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'Please select an image file.']);
    exit();
}

if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error. Please try again.']);
    exit();
}

$file = $_FILES['profile_picture'];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Only allow image files
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($file_ext, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and GIF images are allowed.']);
    exit();
}

// Check file size (max 5MB for profile pictures)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB.']);
    exit();
}

// Create profile pictures directory
$profile_dir = UPLOAD_DIR . 'profile_pictures/';
if (!file_exists($profile_dir)) {
    mkdir($profile_dir, 0777, true);
}

$conn = getDBConnection();

// Get old profile picture
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$old_picture = $result->fetch_assoc()['profile_picture'] ?? null;
$stmt->close();

// Generate unique filename
$new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
$upload_path = $profile_dir . $new_filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Update database
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_filename, $user_id);
    
    if ($stmt->execute()) {
        // Delete old profile picture if exists
        if ($old_picture && file_exists($profile_dir . $old_picture)) {
            unlink($profile_dir . $old_picture);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully!',
            'image_url' => BASE_URL . 'uploads/profile_pictures/' . $new_filename
        ]);
    } else {
        unlink($upload_path);
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file. Please try again.']);
}

closeDBConnection($conn);
?>
