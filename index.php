<?php
require_once 'config/init.php';

$conn = getDBConnection();

// Get statistics
$stats = [
    'users' => 0,
    'resources' => 0,
    'downloads' => 0,
    'departments' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
if ($row = $result->fetch_assoc()) $stats['users'] = $row['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM resources WHERE is_approved = TRUE");
if ($row = $result->fetch_assoc()) $stats['resources'] = $row['count'];

$result = $conn->query("SELECT SUM(downloads_count) as count FROM resources");
if ($row = $result->fetch_assoc()) $stats['downloads'] = $row['count'] ?? 0;

$result = $conn->query("SELECT COUNT(DISTINCT department) as count FROM categories");
if ($row = $result->fetch_assoc()) $stats['departments'] = $row['count'];

// Get recent uploads
$recent_query = "SELECT r.*, u.full_name, c.course_name, c.course_code 
                 FROM resources r 
                 JOIN users u ON r.user_id = u.user_id 
                 JOIN categories c ON r.category_id = c.category_id 
                 WHERE r.is_approved = TRUE 
                 ORDER BY r.uploaded_at DESC 
                 LIMIT 6";
$recent_resources = $conn->query($recent_query);

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JU Campus Notes Hub - Share & Access Academic Resources</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-3xl text-emerald-600"></i>
                    <span class="text-2xl font-bold text-emerald-900">JU Notes Hub</span>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard/user-dashboard.php" class="text-gray-700 hover:text-emerald-600 font-semibold">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="resources/browse.php" class="text-gray-700 hover:text-emerald-600 font-semibold">
                            <i class="fas fa-book"></i> Browse
                        </a>
                        <a href="auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php" class="text-gray-700 hover:text-emerald-600 font-semibold">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="auth/register.php" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-emerald-600 to-purple-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-4">JU Campus Notes & Resource Hub</h1>
            <p class="text-xl mb-8">Your Collaborative Academic Ecosystem</p>
            <p class="text-lg mb-8 max-w-3xl mx-auto">
                Share, organize, and access quality study materials. Join our community of students 
                helping each other succeed through collaborative learning.
            </p>
            
            <!-- Search Bar -->
            <div class="max-w-3xl mx-auto">
                <form action="resources/browse.php" method="GET" class="flex gap-2">
                    <input type="text" name="search" 
                           placeholder="Search for notes, assignments, past papers..." 
                           class="flex-1 px-6 py-4 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300">
                    <button type="submit" class="bg-purple-700 hover:bg-purple-800 px-8 py-4 rounded-lg font-semibold">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>

            <div class="mt-8 flex justify-center gap-4">
                <?php if (!isLoggedIn()): ?>
                    <a href="auth/register.php" class="bg-white text-emerald-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100">
                        Get Started Free
                    </a>
                <?php endif; ?>
                <a href="resources/browse.php" class="bg-transparent border-2 border-white px-8 py-3 rounded-lg font-bold hover:bg-white hover:text-emerald-600">
                    Browse Resources
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-emerald-600 mb-2">
                        <i class="fas fa-users"></i> <?php echo number_format($stats['users']); ?>
                    </div>
                    <div class="text-gray-600 font-semibold">Active Students</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-green-600 mb-2">
                        <i class="fas fa-file-alt"></i> <?php echo number_format($stats['resources']); ?>
                    </div>
                    <div class="text-gray-600 font-semibold">Resources Shared</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-purple-600 mb-2">
                        <i class="fas fa-download"></i> <?php echo number_format($stats['downloads']); ?>
                    </div>
                    <div class="text-gray-600 font-semibold">Total Downloads</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-orange-600 mb-2">
                        <i class="fas fa-building"></i> <?php echo $stats['departments']; ?>
                    </div>
                    <div class="text-gray-600 font-semibold">Departments Covered</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Uploads -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-clock text-blue-500"></i> Recent Uploads
                </h2>
                <a href="resources/browse.php?sort=recent" class="text-emerald-600 hover:text-emerald-800 font-semibold">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($resource = $recent_resources->fetch_assoc()): ?>
                <div class="bg-white border border-gray-200 rounded-lg hover:shadow-lg transition duration-300 p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($resource['title']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($resource['course_code']); ?></p>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">NEW</span>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span><i class="fas fa-calendar"></i> <?php echo timeAgo($resource['uploaded_at']); ?></span>
                        <span><i class="fas fa-file"></i> <?php echo strtoupper($resource['file_type']); ?></span>
                    </div>
                    
                    <a href="resources/view.php?id=<?php echo $resource['resource_id']; ?>" 
                       class="block text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                        View Resource
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gradient-to-br from-emerald-50 to-purple-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Platform Features</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-emerald-600 mb-4"><i class="fas fa-upload"></i></div>
                    <h3 class="text-xl font-bold mb-3">Easy Upload</h3>
                    <p class="text-gray-600">Share your notes, assignments, and study materials with the community effortlessly.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-green-600 mb-4"><i class="fas fa-search"></i></div>
                    <h3 class="text-xl font-bold mb-3">Smart Search</h3>
                    <p class="text-gray-600">Find exactly what you need with advanced filters and full-text search capabilities.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-purple-600 mb-4"><i class="fas fa-users"></i></div>
                    <h3 class="text-xl font-bold mb-3">Community Driven</h3>
                    <p class="text-gray-600">Connect with fellow students, share knowledge, and grow together.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-orange-600 mb-4"><i class="fas fa-bookmark"></i></div>
                    <h3 class="text-xl font-bold mb-3">Bookmarks</h3>
                    <p class="text-gray-600">Save your favorite resources for quick access anytime you need them.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-blue-600 mb-4"><i class="fas fa-comments"></i></div>
                    <h3 class="text-xl font-bold mb-3">Comments & Feedback</h3>
                    <p class="text-gray-600">Engage with resources through comments and help improve quality.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-5xl text-red-600 mb-4"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="text-xl font-bold mb-3">Responsive Design</h3>
                    <p class="text-gray-600">Access resources seamlessly from any device - desktop, tablet, or mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">
                        <i class="fas fa-graduation-cap"></i> JU Notes Hub
                    </h3>
                    <p class="text-gray-400">
                        Empowering students through collaborative learning and resource sharing.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="resources/browse.php" class="text-gray-400 hover:text-white">Browse Resources</a></li>
                        <li><a href="auth/register.php" class="text-gray-400 hover:text-white">Sign Up</a></li>
                        <li><a href="auth/login.php" class="text-gray-400 hover:text-white">Login</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Admin</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-4">Contact Admin</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope"></i> 20220654965shimul1@juniv.edu</li>
                        <li><i class="fas fa-envelope"></i> 20220654976oywon@juniv.edu</li>
                        <li><i class="fas fa-envelope"></i> 20220654977ahad@juniv.edu</li>
                        <li><i class="fas fa-envelope"></i> 20220655000nusaiba@juniv.edu</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 JU Campus Notes Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
