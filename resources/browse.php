<?php
require_once '../config/init.php';

$conn = getDBConnection();

// Get user info if logged in
$user = null;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$faculty = isset($_GET['faculty']) ? sanitizeInput($_GET['faculty']) : '';
$department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : '';
$resource_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'recent';
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Build query
$where_clauses = ["r.is_approved = TRUE"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_clauses[] = "(r.title LIKE ? OR r.description LIKE ? OR c.course_name LIKE ? OR c.course_code LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ssss";
}

if (!empty($faculty)) {
    $where_clauses[] = "c.faculty = ?";
    $params[] = &$faculty;
    $types .= "s";
}

if (!empty($department)) {
    $where_clauses[] = "c.department = ?";
    $params[] = &$department;
    $types .= "s";
}

if (!empty($resource_type)) {
    $where_clauses[] = "r.resource_type = ?";
    $params[] = &$resource_type;
    $types .= "s";
}

if ($user_filter > 0) {
    $where_clauses[] = "r.user_id = ?";
    $params[] = &$user_filter;
    $types .= "i";
}

$where_sql = implode(" AND ", $where_clauses);

// Order by clause
$order_by = "r.uploaded_at DESC";
switch ($sort) {
    case 'popular':
        $order_by = "r.downloads_count DESC, r.uploaded_at DESC";
        break;
    case 'views':
        $order_by = "r.views_count DESC, r.uploaded_at DESC";
        break;
    case 'recent':
    default:
        $order_by = "r.uploaded_at DESC";
        break;
}

$query = "SELECT r.*, u.full_name, c.course_name, c.course_code, c.faculty, c.department 
          FROM resources r 
          JOIN users u ON r.user_id = u.user_id 
          JOIN categories c ON r.category_id = c.category_id 
          WHERE $where_sql 
          ORDER BY $order_by";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resources = $stmt->get_result();

// Get unique faculties and departments for filters
$faculties_result = $conn->query("SELECT DISTINCT faculty FROM categories ORDER BY faculty");
$departments_result = $conn->query("SELECT DISTINCT department FROM categories ORDER BY department");

$stmt->close();
closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Resources - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="../index.php" class="flex items-center space-x-2">
                        <i class="fas fa-graduation-cap text-3xl text-emerald-600"></i>
                        <span class="text-2xl font-bold text-emerald-900">JU Notes Hub</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="../dashboard/user-dashboard.php" class="text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="upload.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                            <i class="fas fa-upload"></i> Upload
                        </a>
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 hidden z-50">
                                <a href="../dashboard/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <a href="../dashboard/settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="../contact.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-envelope"></i> Contact Admin
                                </a>
                                <hr class="my-2">
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../auth/login.php" class="text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="../auth/register.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-book"></i> Browse Resources
            </h1>
            <p class="text-gray-600">Discover and download academic materials shared by your peers</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <form method="GET" action="" class="space-y-4">
                <!-- Search Bar -->
                <div>
                    <input type="text" name="search" 
                           placeholder="Search for notes, assignments, past papers..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Filters Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <select name="faculty" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Faculties</option>
                        <?php while ($f = $faculties_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($f['faculty']); ?>" <?php echo $faculty === $f['faculty'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($f['faculty']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="department" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Departments</option>
                        <?php while ($d = $departments_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($d['department']); ?>" <?php echo $department === $d['department'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="type" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Types</option>
                        <option value="notes" <?php echo $resource_type === 'notes' ? 'selected' : ''; ?>>Notes</option>
                        <option value="assignment" <?php echo $resource_type === 'assignment' ? 'selected' : ''; ?>>Assignments</option>
                        <option value="past_questions" <?php echo $resource_type === 'past_questions' ? 'selected' : ''; ?>>Past Questions</option>
                        <option value="lecture_slides" <?php echo $resource_type === 'lecture_slides' ? 'selected' : ''; ?>>Lecture Slides</option>
                        <option value="book" <?php echo $resource_type === 'book' ? 'selected' : ''; ?>>Books</option>
                        <option value="other" <?php echo $resource_type === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>

                    <select name="sort" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Downloaded</option>
                        <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>Most Viewed</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="browse.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="mb-4 flex justify-between items-center">
            <p class="text-gray-600">
                <i class="fas fa-info-circle"></i> 
                Found <strong><?php echo $resources->num_rows; ?></strong> resource(s)
            </p>
        </div>

        <?php if ($resources->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($resource = $resources->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                    <!-- Resource Type Badge -->
                    <div class="bg-gradient-to-r from-emerald-600 to-purple-600 text-white px-4 py-2">
                        <span class="text-sm font-semibold">
                            <i class="fas fa-tag"></i> <?php echo strtoupper($resource['resource_type']); ?>
                        </span>
                    </div>

                    <div class="p-6">
                        <!-- Title -->
                        <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2">
                            <?php echo htmlspecialchars($resource['title']); ?>
                        </h3>

                        <!-- Course Info -->
                        <p class="text-sm text-emerald-600 font-semibold mb-2">
                            <?php 
                            // Only show course name if it's different from course code
                            if ($resource['course_code'] !== $resource['course_name']) {
                                echo htmlspecialchars($resource['course_code']) . ' - ' . htmlspecialchars($resource['course_name']);
                            } else {
                                echo htmlspecialchars($resource['course_code']);
                            }
                            ?>
                        </p>

                        <!-- Department -->
                        <p class="text-xs text-gray-600 mb-3">
                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($resource['department']); ?>
                        </p>

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            <?php echo htmlspecialchars(substr($resource['description'], 0, 150)) . (strlen($resource['description']) > 150 ? '...' : ''); ?>
                        </p>

                        <!-- Meta Info -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span title="Uploaded by">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($resource['full_name']); ?>
                            </span>
                            <span title="File type">
                                <i class="fas fa-file"></i> <?php echo strtoupper($resource['file_type']); ?>
                            </span>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b">
                            <span><i class="fas fa-download"></i> <?php echo $resource['downloads_count']; ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo $resource['views_count']; ?></span>
                            <span title="<?php echo $resource['uploaded_at']; ?>">
                                <i class="fas fa-clock"></i> <?php echo timeAgo($resource['uploaded_at']); ?>
                            </span>
                        </div>

                        <!-- Action Button -->
                        <a href="view.php?id=<?php echo $resource['resource_id']; ?>" 
                           class="block text-center bg-emerald-600 text-white py-2 rounded-lg hover:bg-emerald-700 transition">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Resources Found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your search criteria or filters</p>
                <a href="browse.php" class="inline-block bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700">
                    <i class="fas fa-redo"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // User menu dropdown toggle
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');
        
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('hidden');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
