<?php
require_once '../config/init.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit();
}

$conn = getDBConnection();

// Search in resources and categories
$search_param = "%$query%";
$stmt = $conn->prepare("
    SELECT DISTINCT 
        r.resource_id,
        r.title,
        c.course_code,
        c.course_name,
        r.resource_type
    FROM resources r
    JOIN categories c ON r.category_id = c.category_id
    WHERE r.is_approved = TRUE 
    AND (
        r.title LIKE ? OR 
        r.description LIKE ? OR 
        c.course_name LIKE ? OR 
        c.course_code LIKE ?
    )
    LIMIT 10
");

$stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    // Only show course name if it's different from course code
    $course_display = ($row['course_code'] !== $row['course_name']) 
        ? $row['course_code'] . ' - ' . $row['course_name'] 
        : $row['course_code'];
    
    $results[] = [
        'id' => $row['resource_id'],
        'title' => $row['title'],
        'course' => $course_display,
        'type' => $row['resource_type']
    ];
}

$stmt->close();
closeDBConnection($conn);

echo json_encode(['results' => $results]);
?>
