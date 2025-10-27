<?php
require_once '../config/init.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user statistics
$stats = [
    'uploads' => 0,
    'downloads' => 0,
    'bookmarks' => 0,
    'comments' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM resources WHERE user_id = $user_id");
if ($row = $result->fetch_assoc()) $stats['uploads'] = $row['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM downloads WHERE user_id = $user_id");
if ($row = $result->fetch_assoc()) $stats['downloads'] = $row['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM bookmarks WHERE user_id = $user_id");
if ($row = $result->fetch_assoc()) $stats['bookmarks'] = $row['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id");
if ($row = $result->fetch_assoc()) $stats['comments'] = $row['count'];

// Get user's recent uploads
$recent_uploads_query = "SELECT r.*, c.course_name, c.course_code 
                        FROM resources r 
                        JOIN categories c ON r.category_id = c.category_id 
                        WHERE r.user_id = ? 
                        ORDER BY r.uploaded_at DESC 
                        LIMIT 5";
$stmt = $conn->prepare($recent_uploads_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_uploads = $stmt->get_result();
$stmt->close();

// Get user's bookmarks
$bookmarks_query = "SELECT r.*, c.course_name, c.course_code, u.full_name, b.created_at as bookmarked_at 
                   FROM bookmarks b 
                   JOIN resources r ON b.resource_id = r.resource_id 
                   JOIN categories c ON r.category_id = c.category_id 
                   JOIN users u ON r.user_id = u.user_id 
                   WHERE b.user_id = ? 
                   ORDER BY b.created_at DESC 
                   LIMIT 5";
$stmt = $conn->prepare($bookmarks_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookmarks = $stmt->get_result();
$stmt->close();

// Get recent activity (downloads)
$activity_query = "SELECT r.title, r.resource_id, d.downloaded_at, c.course_code 
                  FROM downloads d 
                  JOIN resources r ON d.resource_id = r.resource_id 
                  JOIN categories c ON r.category_id = c.category_id 
                  WHERE d.user_id = ? 
                  ORDER BY d.downloaded_at DESC 
                  LIMIT 5";
$stmt = $conn->prepare($activity_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_activity = $stmt->get_result();
$stmt->close();

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JU Campus Notes Hub</title>
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
                <div class="flex items-center space-x-6">
                    <a href="user-dashboard.php" class="text-emerald-600 font-semibold">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="../resources/browse.php" class="text-gray-700 hover:text-emerald-600">
                        <i class="fas fa-book"></i> Browse
                    </a>
                    <a href="../resources/upload.php" class="text-gray-700 hover:text-emerald-600">
                        <i class="fas fa-upload"></i> Upload
                    </a>
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
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
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-emerald-600 to-purple-600 text-white rounded-xl p-8 mb-8">
            <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>! 👋</h1>
            <p class="text-emerald-100">
                <?php echo htmlspecialchars($user['department']); ?> • Batch <?php echo htmlspecialchars($user['batch']); ?>
            </p>
            <p class="mt-4 text-sm">Last login: <?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'First time'; ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">My Uploads</p>
                        <p class="text-3xl font-bold text-emerald-600"><?php echo $stats['uploads']; ?></p>
                    </div>
                    <div class="bg-emerald-100 rounded-full p-4">
                        <i class="fas fa-upload text-2xl text-emerald-600"></i>
                    </div>
                </div>
                <a href="../resources/browse.php?user=<?php echo $user_id; ?>" class="text-sm text-emerald-600 hover:text-emerald-800 mt-2 inline-block">
                    View all <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Downloads</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['downloads']; ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-download text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Bookmarks</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['bookmarks']; ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-bookmark text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Comments</p>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $stats['comments']; ?></p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-comments text-2xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="../resources/upload.php" class="flex items-center space-x-3 p-4 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                    <div class="bg-emerald-600 text-white rounded-full p-3">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Upload Resource</p>
                        <p class="text-sm text-gray-600">Share your notes</p>
                    </div>
                </a>

                <a href="../resources/browse.php" class="flex items-center space-x-3 p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <div class="bg-green-600 text-white rounded-full p-3">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Browse Resources</p>
                        <p class="text-sm text-gray-600">Find study materials</p>
                    </div>
                </a>

                <a href="profile.php" class="flex items-center space-x-3 p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <div class="bg-purple-600 text-white rounded-full p-3">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Edit Profile</p>
                        <p class="text-sm text-gray-600">Update your info</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- My Recent Uploads -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-file-upload text-emerald-600"></i> My Recent Uploads
                    </h2>
                    <a href="../resources/browse.php?user=<?php echo $user_id; ?>" class="text-emerald-600 hover:text-emerald-800 text-sm">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <?php if ($recent_uploads->num_rows > 0): ?>
                    <div class="space-y-3">
                        <?php while ($upload = $recent_uploads->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($upload['title']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($upload['course_code']); ?></p>
                                </div>
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded">
                                    <?php echo strtoupper($upload['resource_type']); ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
                                <span><i class="fas fa-download"></i> <?php echo $upload['downloads_count']; ?> downloads</span>
                                <span><i class="fas fa-eye"></i> <?php echo $upload['views_count']; ?> views</span>
                                <span><?php echo timeAgo($upload['uploaded_at']); ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-folder-open text-5xl mb-3"></i>
                        <p>You haven't uploaded any resources yet.</p>
                        <a href="../resources/upload.php" class="text-emerald-600 hover:text-emerald-800 mt-2 inline-block">
                            Upload your first resource <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bookmarked Resources -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-bookmark text-purple-600"></i> My Bookmarks
                    </h2>
                </div>

                <?php if ($bookmarks->num_rows > 0): ?>
                    <div class="space-y-3">
                        <?php while ($bookmark = $bookmarks->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($bookmark['title']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($bookmark['course_code']); ?> • by <?php echo htmlspecialchars($bookmark['full_name']); ?></p>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-clock"></i> Saved <?php echo timeAgo($bookmark['bookmarked_at']); ?>
                                </span>
                                <a href="../resources/view.php?id=<?php echo $bookmark['resource_id']; ?>" 
                                   class="text-emerald-600 hover:text-emerald-800 text-sm">
                                    View <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-bookmark text-5xl mb-3"></i>
                        <p>No bookmarked resources yet.</p>
                        <a href="../resources/browse.php" class="text-emerald-600 hover:text-emerald-800 mt-2 inline-block">
                            Browse resources <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-history text-green-600"></i> Recent Activity
                </h2>

                <?php if ($recent_activity->num_rows > 0): ?>
                    <div class="space-y-3">
                        <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                        <div class="flex items-center justify-between border-l-4 border-green-500 bg-gray-50 p-4 rounded-r-lg">
                            <div class="flex items-center space-x-3">
                                <div class="bg-green-100 text-green-600 rounded-full p-2">
                                    <i class="fas fa-download"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Downloaded: <?php echo htmlspecialchars($activity['title']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($activity['course_code']); ?></p>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500"><?php echo timeAgo($activity['downloaded_at']); ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-5xl mb-3"></i>
                        <p>No recent activity to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
