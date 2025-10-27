<?php
require_once '../config/init.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $full_name = sanitizeInput($_POST['full_name']);
        $mobile_number = sanitizeInput($_POST['mobile_number']);
        $batch = sanitizeInput($_POST['batch']);
        
        if (empty($full_name)) {
            $error = 'Full name is required.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, mobile_number = ?, batch = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $full_name, $mobile_number, $batch, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Profile updated successfully!';
                $_SESSION['user_name'] = $full_name;
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update profile.';
            }
            $stmt->close();
        }
    }
    // Note: Password change removed - using Google OAuth only
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - JU Campus Notes Hub</title>
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
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-cog"></i> Account Settings
                </h1>
                <p class="text-gray-600">Manage your profile and account preferences</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Profile Settings -->
            <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user"></i> Profile Information
                </h2>
                
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                        <input type="text" name="full_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email (Cannot be changed)</label>
                        <input type="email" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Faculty (Cannot be changed)</label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                   value="<?php echo htmlspecialchars($user['faculty']); ?>" disabled>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Department (Cannot be changed)</label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                   value="<?php echo htmlspecialchars($user['department']); ?>" disabled>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Batch</label>
                            <select name="batch" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <?php for ($i = 54; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($user['batch'] == $i) ? 'selected' : ''; ?>>
                                        Batch <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Mobile Number</label>
                            <input type="tel" name="mobile_number" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   value="<?php echo htmlspecialchars($user['mobile_number']); ?>">
                        </div>
                    </div>

                    <button type="submit" 
                            class="bg-emerald-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-emerald-700 transition">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Authentication Info -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-shield-alt"></i> Authentication
                </h2>
                
                <div class="flex items-start gap-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-2xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Google OAuth Enabled</p>
                        <p class="text-sm text-gray-600">Your account is secured with Google authentication. No password management needed.</p>
                        <p class="text-sm text-green-600 mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Signed in with: <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                        </p>
                    </div>
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
