<?php
require_once '../config/init.php';
requireLogin();

$conn = getDBConnection();

// Get overall platform statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total resources
$result = $conn->query("SELECT COUNT(*) as count FROM resources WHERE is_approved = TRUE");
$stats['total_resources'] = $result->fetch_assoc()['count'];

// Total downloads
$result = $conn->query("SELECT COUNT(*) as count FROM downloads");
$stats['total_downloads'] = $result->fetch_assoc()['count'];

// Total comments
$result = $conn->query("SELECT COUNT(*) as count FROM comments");
$stats['total_comments'] = $result->fetch_assoc()['count'];

// Top 10 most downloaded resources
$top_resources_query = "SELECT r.title, r.downloads_count, u.full_name, c.course_code 
                        FROM resources r 
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN categories c ON r.category_id = c.category_id 
                        WHERE r.is_approved = TRUE 
                        ORDER BY r.downloads_count DESC 
                        LIMIT 10";
$top_resources = $conn->query($top_resources_query);

// Resources by type
$type_query = "SELECT resource_type, COUNT(*) as count 
               FROM resources 
               WHERE is_approved = TRUE 
               GROUP BY resource_type";
$resources_by_type = $conn->query($type_query);

// Recent uploads (last 7 days)
$recent_query = "SELECT COUNT(*) as count 
                 FROM resources 
                 WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                 AND is_approved = TRUE";
$result = $conn->query($recent_query);
$stats['recent_uploads'] = $result->fetch_assoc()['count'];

// Most active users
$active_users_query = "SELECT u.full_name, u.department, COUNT(r.resource_id) as upload_count 
                       FROM users u 
                       JOIN resources r ON u.user_id = r.user_id 
                       WHERE u.is_active = TRUE 
                       GROUP BY u.user_id 
                       ORDER BY upload_count DESC 
                       LIMIT 10";
$active_users = $conn->query($active_users_query);

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Analytics - JU Campus Notes Hub</title>
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
                    <a href="user-dashboard.php" class="text-gray-700 hover:text-emerald-600">
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
                            <span>User Menu</span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 hidden z-50">
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-line"></i> Platform Analytics
            </h1>
            <p class="text-gray-600">Overview of platform statistics and trends</p>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                    <span class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_users']); ?></span>
                </div>
                <p class="text-gray-600 font-semibold">Total Users</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-file-alt text-2xl text-green-600"></i>
                    </div>
                    <span class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_resources']); ?></span>
                </div>
                <p class="text-gray-600 font-semibold">Total Resources</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-download text-2xl text-purple-600"></i>
                    </div>
                    <span class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_downloads']); ?></span>
                </div>
                <p class="text-gray-600 font-semibold">Total Downloads</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-clock text-2xl text-orange-600"></i>
                    </div>
                    <span class="text-3xl font-bold text-orange-600"><?php echo number_format($stats['recent_uploads']); ?></span>
                </div>
                <p class="text-gray-600 font-semibold">Uploads (7 days)</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Resources -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-fire text-orange-500"></i> Top Downloaded Resources
                </h2>
                <div class="space-y-3">
                    <?php while ($resource = $top_resources->fetch_assoc()): ?>
                    <div class="flex items-center justify-between border-b pb-3">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($resource['title']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($resource['course_code']); ?> • by <?php echo htmlspecialchars($resource['full_name']); ?></p>
                        </div>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo number_format($resource['downloads_count']); ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Most Active Users -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-trophy text-yellow-500"></i> Most Active Contributors
                </h2>
                <div class="space-y-3">
                    <?php $rank = 1; while ($user = $active_users->fetch_assoc()): ?>
                    <div class="flex items-center justify-between border-b pb-3">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl font-bold text-emerald-600">#<?php echo $rank++; ?></span>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['department']); ?></p>
                            </div>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo $user['upload_count']; ?> uploads
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Resources by Type -->
            <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie"></i> Resources by Type
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <?php 
                    $colors = ['indigo', 'green', 'purple', 'orange', 'blue'];
                    $index = 0;
                    while ($type = $resources_by_type->fetch_assoc()): 
                        $color = $colors[$index % count($colors)];
                    ?>
                    <div class="text-center p-4 bg-<?php echo $color; ?>-50 rounded-lg">
                        <p class="text-3xl font-bold text-<?php echo $color; ?>-600 mb-2"><?php echo number_format($type['count']); ?></p>
                        <p class="text-sm text-gray-700 font-semibold capitalize"><?php echo str_replace('_', ' ', $type['resource_type']); ?></p>
                    </div>
                    <?php $index++; endwhile; ?>
                </div>
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
