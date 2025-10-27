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
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM resources WHERE user_id = ?) as total_uploads,
    (SELECT SUM(downloads_count) FROM resources WHERE user_id = ?) as total_downloads,
    (SELECT COUNT(*) FROM bookmarks WHERE user_id = ?) as total_bookmarks,
    (SELECT COUNT(*) FROM comments WHERE user_id = ?) as total_comments";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's uploads
$uploads_query = "SELECT r.*, c.course_name, c.course_code 
                  FROM resources r 
                  JOIN categories c ON r.category_id = c.category_id 
                  WHERE r.user_id = ? 
                  ORDER BY r.uploaded_at DESC";
$stmt = $conn->prepare($uploads_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$uploads = $stmt->get_result();
$stmt->close();

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - JU Campus Notes Hub</title>
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
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
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
        <div class="max-w-6xl mx-auto">
            <!-- Profile Header -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-emerald-600 to-purple-600 h-32"></div>
                <div class="px-8 pb-8">
                    <div class="flex items-end -mt-16 mb-6">
                        <div class="bg-white rounded-full p-2 shadow-lg relative group">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <?php
                                    // Use Base64 encoding for reliable image display
                                    $file_path = dirname(__DIR__) . '/uploads/profile_pictures/' . $user['profile_picture'];
                                    if (file_exists($file_path)) {
                                        $image_data = base64_encode(file_get_contents($file_path));
                                        $image_info = @getimagesize($file_path);
                                        $mime_type = $image_info ? $image_info['mime'] : 'image/jpeg';
                                        $image_src = 'data:' . $mime_type . ';base64,' . $image_data;
                                    } else {
                                        $image_src = '';
                                    }
                                ?>
                                <?php if (!empty($image_src)): ?>
                                    <img src="<?php echo $image_src; ?>" 
                                         alt="Profile" class="w-32 h-32 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="bg-emerald-100 rounded-full p-6 w-32 h-32 flex items-center justify-center">
                                        <i class="fas fa-user text-5xl text-emerald-600"></i>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="bg-emerald-100 rounded-full p-6 w-32 h-32 flex items-center justify-center">
                                    <i class="fas fa-user text-5xl text-emerald-600"></i>
                                </div>
                            <?php endif; ?>
                            <label for="profile_picture_upload" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                                <i class="fas fa-camera text-white text-2xl"></i>
                            </label>
                            <input type="file" id="profile_picture_upload" accept="image/*" class="hidden">
                        </div>
                        <div class="ml-6 pb-2">
                            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                            <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="ml-auto pb-2">
                            <a href="settings.php" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                                <i class="fas fa-cog"></i> Edit Profile
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Faculty</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['faculty']); ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Department</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['department']); ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Batch</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['batch']); ?></p>
                        </div>
                        <?php if (!empty($user['mobile_number'])): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Mobile</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['mobile_number']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Member Since</p>
                            <p class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Last Login</p>
                            <p class="font-semibold text-gray-800"><?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <div class="text-4xl font-bold text-emerald-600 mb-2"><?php echo $stats['total_uploads']; ?></div>
                    <div class="text-gray-600">Uploads</div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2"><?php echo $stats['total_downloads'] ?? 0; ?></div>
                    <div class="text-gray-600">Total Downloads</div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <div class="text-4xl font-bold text-purple-600 mb-2"><?php echo $stats['total_bookmarks']; ?></div>
                    <div class="text-gray-600">Bookmarks</div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <div class="text-4xl font-bold text-orange-600 mb-2"><?php echo $stats['total_comments']; ?></div>
                    <div class="text-gray-600">Comments</div>
                </div>
            </div>

            <!-- My Notes -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-file-alt"></i> My Notes & Resources
                </h2>

                <?php if ($uploads->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Course</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Downloads</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Views</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Uploaded</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($upload = $uploads->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($upload['title']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($upload['course_code']); ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded">
                                            <?php echo strtoupper($upload['resource_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        <?php echo $upload['downloads_count']; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        <?php echo $upload['views_count']; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo timeAgo($upload['uploaded_at']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="../resources/view.php?id=<?php echo $upload['resource_id']; ?>" 
                                           class="text-emerald-600 hover:text-emerald-800">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-folder-open text-6xl mb-4"></i>
                        <p class="text-xl mb-4">You haven't uploaded any resources yet.</p>
                        <a href="../resources/upload.php" class="inline-block bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700">
                            <i class="fas fa-upload"></i> Upload Your First Resource
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Profile picture upload
        document.getElementById('profile_picture_upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5MB.');
                return;
            }
            
            const formData = new FormData();
            formData.append('profile_picture', file);
            
            // Show loading
            const uploadBtn = document.querySelector('label[for="profile_picture_upload"]');
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-white text-2xl"></i>';
            
            fetch('../api/upload-profile-picture.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                    uploadBtn.innerHTML = '<i class="fas fa-camera text-white text-2xl"></i>';
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
                uploadBtn.innerHTML = '<i class="fas fa-camera text-white text-2xl"></i>';
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
