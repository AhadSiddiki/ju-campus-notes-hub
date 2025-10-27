<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to bookmark.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$resource_id = isset($_POST['resource_id']) ? intval($_POST['resource_id']) : 0;

if ($resource_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid resource.']);
    exit();
}

$conn = getDBConnection();

// Check if bookmark exists
$stmt = $conn->prepare("SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND resource_id = ?");
$stmt->bind_param("ii", $user_id, $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove bookmark
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $user_id, $resource_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Bookmark removed.']);
} else {
    // Add bookmark
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, resource_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $resource_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Resource bookmarked!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to bookmark resource.']);
    }
}

$stmt->close();
closeDBConnection($conn);
?>
