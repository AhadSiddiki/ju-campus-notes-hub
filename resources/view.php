<?php
require_once '../config/init.php';

$resource_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get logged in user info
$logged_user = null;
if (isLoggedIn()) {
    $temp_conn = getDBConnection();
    $temp_stmt = $temp_conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $temp_user_id = $_SESSION['user_id'];
    $temp_stmt->bind_param("i", $temp_user_id);
    $temp_stmt->execute();
    $logged_user = $temp_stmt->get_result()->fetch_assoc();
    $temp_stmt->close();
    closeDBConnection($temp_conn);
}

if ($resource_id === 0) {
    header('Location: browse.php');
    exit();
}

$conn = getDBConnection();

// Increment view count
$conn->query("UPDATE resources SET views_count = views_count + 1 WHERE resource_id = $resource_id");

// Get resource details
$query = "SELECT r.*, u.full_name, u.email, u.department as user_dept, c.course_name, c.course_code, c.faculty, c.department 
          FROM resources r 
          JOIN users u ON r.user_id = u.user_id 
          JOIN categories c ON r.category_id = c.category_id 
          WHERE r.resource_id = ? AND r.is_approved = TRUE";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: browse.php');
    exit();
}

$resource = $result->fetch_assoc();
$stmt->close();

// Check if user has bookmarked this resource
$is_bookmarked = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $user_id, $resource_id);
    $stmt->execute();
    $is_bookmarked = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Get comments
$comments_query = "SELECT c.*, u.full_name, u.department 
                   FROM comments c 
                   JOIN users u ON c.user_id = u.user_id 
                   WHERE c.resource_id = ? 
                   ORDER BY c.created_at DESC";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();

// Get related resources (same course)
$related_query = "SELECT r.*, u.full_name 
                  FROM resources r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.category_id = ? AND r.resource_id != ? AND r.is_approved = TRUE 
                  ORDER BY r.downloads_count DESC 
                  LIMIT 3";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("ii", $resource['category_id'], $resource_id);
$stmt->execute();
$related_resources = $stmt->get_result();
$stmt->close();

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resource['title']); ?> - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                        <a href="browse.php" class="text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-book"></i> Browse
                        </a>
                        <a href="upload.php" class="text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-upload"></i> Upload
                        </a>
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span><?php echo htmlspecialchars($logged_user['full_name'] ?? 'User'); ?></span>
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
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="text-gray-600">
                <a href="../index.php" class="hover:text-emerald-600">Home</a> / 
                <a href="browse.php" class="hover:text-emerald-600">Resources</a> / 
                <span class="text-gray-800"><?php echo htmlspecialchars($resource['title']); ?></span>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Resource Details Card -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <!-- Resource Type Badge -->
                    <div class="mb-4">
                        <span class="bg-emerald-100 text-emerald-800 px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-tag"></i> <?php 
                                $type_labels = [
                                    'notes' => 'NOTES',
                                    'assignment' => 'ASSIGNMENT',
                                    'past_questions' => 'PAST QUESTIONS',
                                    'lecture_slides' => 'LECTURE SLIDES',
                                    'book' => 'BOOK',
                                    'other' => 'OTHER'
                                ];
                                echo $type_labels[$resource['resource_type']] ?? strtoupper($resource['resource_type']);
                            ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-gray-800 mb-4">
                        <?php echo htmlspecialchars($resource['title']); ?>
                    </h1>

                    <!-- Course Info -->
                    <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 mb-6">
                        <p class="text-lg font-semibold text-emerald-900">
                            <?php 
                            // Only show course name if it's different from course code
                            if ($resource['course_code'] !== $resource['course_name']) {
                                echo htmlspecialchars($resource['course_code']) . ' - ' . htmlspecialchars($resource['course_name']);
                            } else {
                                echo htmlspecialchars($resource['course_code']);
                            }
                            ?>
                        </p>
                        <p class="text-sm text-emerald-700">
                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($resource['department']); ?>
                        </p>
                        <p class="text-sm text-emerald-700">
                            <i class="fas fa-university"></i> <?php echo htmlspecialchars($resource['faculty']); ?>
                        </p>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center gap-6 mb-6 text-gray-600">
                        <span><i class="fas fa-download"></i> <?php echo $resource['downloads_count']; ?> downloads</span>
                        <span><i class="fas fa-eye"></i> <?php echo $resource['views_count']; ?> views</span>
                        <span><i class="fas fa-calendar"></i> <?php echo timeAgo($resource['uploaded_at']); ?></span>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($resource['description'])): ?>
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-3">Description</h2>
                        <p class="text-gray-600 whitespace-pre-line"><?php echo htmlspecialchars($resource['description']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Uploader Info -->
                    <div class="border-t pt-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-3">Uploaded By</h2>
                        <div class="flex items-center space-x-3">
                            <div class="bg-emerald-100 rounded-full p-3">
                                <i class="fas fa-user text-2xl text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($resource['full_name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($resource['user_dept']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-xl shadow-md p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-comments"></i> Comments (<?php echo $comments->num_rows; ?>)
                    </h2>

                    <?php if (isLoggedIn()): ?>
                    <!-- Add Comment Form -->
                    <div class="mb-6">
                        <form id="commentForm" class="space-y-4">
                            <input type="hidden" name="resource_id" value="<?php echo $resource_id; ?>">
                            <textarea name="comment_text" id="commentText" rows="3" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                      placeholder="Add a comment..."></textarea>
                            <button type="submit" 
                                    class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </form>
                        <div id="commentMessage" class="mt-3"></div>
                    </div>
                    <?php else: ?>
                    <div class="mb-6 bg-gray-100 border border-gray-300 rounded-lg p-4 text-center">
                        <p class="text-gray-600">
                            <a href="../auth/login.php" class="text-emerald-600 hover:text-emerald-800 font-semibold">Login</a> 
                            to post a comment
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <div id="commentsList" class="space-y-4">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="border-l-4 border-emerald-300 bg-gray-50 p-4 rounded-r-lg">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-emerald-100 rounded-full p-2">
                                        <i class="fas fa-user text-emerald-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($comment['full_name']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($comment['department']); ?></p>
                                            </div>
                                            <span class="text-sm text-gray-500"><?php echo timeAgo($comment['created_at']); ?></span>
                                        </div>
                                        <p class="text-gray-700"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        <?php if (isLoggedIn()): ?>
                        <!-- Download Button -->
                        <a href="../api/download-handler.php?id=<?php echo $resource_id; ?>" 
                           class="block text-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-download"></i> Download File
                        </a>

                        <!-- Bookmark Button -->
                        <button id="bookmarkBtn" data-resource-id="<?php echo $resource_id; ?>" 
                                class="w-full <?php echo $is_bookmarked ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-gray-200 hover:bg-gray-300'; ?> text-gray-800 py-3 rounded-lg transition">
                            <i class="fas fa-bookmark"></i> 
                            <span id="bookmarkText"><?php echo $is_bookmarked ? 'Bookmarked' : 'Bookmark'; ?></span>
                        </button>
                        <?php else: ?>
                        <a href="../auth/login.php" 
                           class="block text-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700">
                            <i class="fas fa-sign-in-alt"></i> Login to Download
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- File Info -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="font-semibold text-gray-800 mb-3">File Information</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">File Type:</span>
                                <span class="font-semibold"><?php echo strtoupper($resource['file_type']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">File Size:</span>
                                <span class="font-semibold"><?php echo formatFileSize($resource['file_size']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Uploaded:</span>
                                <span class="font-semibold"><?php echo date('M d, Y', strtotime($resource['uploaded_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Resources -->
                <?php if ($related_resources->num_rows > 0): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-link"></i> Related Resources
                    </h3>
                    
                    <div class="space-y-4">
                        <?php while ($related = $related_resources->fetch_assoc()): ?>
                        <a href="view.php?id=<?php echo $related['resource_id']; ?>" 
                           class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition">
                            <h4 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-2">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </h4>
                            <p class="text-xs text-gray-600">by <?php echo htmlspecialchars($related['full_name']); ?></p>
                            <div class="flex items-center gap-3 text-xs text-gray-500 mt-2">
                                <span><i class="fas fa-download"></i> <?php echo $related['downloads_count']; ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $related['views_count']; ?></span>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Comment Form Handler
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '../api/comment-handler.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#commentMessage').html(
                            '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' +
                            '<i class="fas fa-check-circle"></i> ' + response.message +
                            '</div>'
                        );
                        $('#commentText').val('');
                        
                        // Reload page after 1 second to show new comment
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        $('#commentMessage').html(
                            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' +
                            '<i class="fas fa-exclamation-circle"></i> ' + response.message +
                            '</div>'
                        );
                    }
                },
                error: function() {
                    $('#commentMessage').html(
                        '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' +
                        'An error occurred. Please try again.' +
                        '</div>'
                    );
                }
            });
        });

        // Bookmark Button Handler
        $('#bookmarkBtn').on('click', function() {
            const resourceId = $(this).data('resource-id');
            
            $.ajax({
                url: '../api/bookmark-handler.php',
                method: 'POST',
                data: { resource_id: resourceId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.action === 'added') {
                            $('#bookmarkBtn').removeClass('bg-gray-200 hover:bg-gray-300')
                                           .addClass('bg-yellow-500 hover:bg-yellow-600');
                            $('#bookmarkText').text('Bookmarked');
                        } else {
                            $('#bookmarkBtn').removeClass('bg-yellow-500 hover:bg-yellow-600')
                                           .addClass('bg-gray-200 hover:bg-gray-300');
                            $('#bookmarkText').text('Bookmark');
                        }
                    }
                }
            });
        });

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
