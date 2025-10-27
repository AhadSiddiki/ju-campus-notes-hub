<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$resource_id = isset($_POST['resource_id']) ? intval($_POST['resource_id']) : 0;
$comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';

if ($resource_id === 0 || empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid comment.']);
    exit();
}

$conn = getDBConnection();

// Verify resource exists
$stmt = $conn->prepare("SELECT resource_id FROM resources WHERE resource_id = ? AND is_approved = TRUE");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resource not found.']);
    $stmt->close();
    closeDBConnection($conn);
    exit();
}
$stmt->close();

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (resource_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $resource_id, $user_id, $comment_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment posted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to post comment. Please try again.']);
}

$stmt->close();
closeDBConnection($conn);
?>
