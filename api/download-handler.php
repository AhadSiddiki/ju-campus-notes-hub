<?php
require_once '../config/init.php';
requireLogin();

$resource_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resource_id === 0) {
    header('Location: ../resources/browse.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get resource information
$stmt = $conn->prepare("SELECT file_name, file_path FROM resources WHERE resource_id = ? AND is_approved = TRUE");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../resources/browse.php');
    exit();
}

$resource = $result->fetch_assoc();
$stmt->close();

$file_path = UPLOAD_DIR . $resource['file_path'];

if (!file_exists($file_path)) {
    die('File not found.');
}

// Log download
$stmt = $conn->prepare("INSERT INTO downloads (user_id, resource_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $resource_id);
$stmt->execute();
$stmt->close();

// Increment download count
$conn->query("UPDATE resources SET downloads_count = downloads_count + 1 WHERE resource_id = $resource_id");

closeDBConnection($conn);

// Serve file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $resource['file_name'] . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($file_path);
exit();
?>
