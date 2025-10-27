<?php
require_once 'config/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-graduation-cap text-3xl text-emerald-600"></i>
                        <span class="text-2xl font-bold text-emerald-900">JU Notes Hub</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard/user-dashboard.php" class="text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-envelope"></i> Contact Admin
                </h1>
                <p class="text-gray-600 text-lg">Have questions? Reach out to our admin team</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-users-cog"></i> Admin Team</h2>
                
                <div class="space-y-4">
                    <div class="border-l-4 border-emerald-600 bg-emerald-50 p-4 rounded-r-lg">
                        <h3 class="font-bold text-gray-800 mb-2">Shimul</h3>
                        <a href="mailto:20220654965shimul1@juniv.edu" class="text-emerald-600 hover:text-emerald-800">
                            <i class="fas fa-envelope"></i> 20220654965shimul1@juniv.edu
                        </a>
                    </div>

                    <div class="border-l-4 border-green-600 bg-green-50 p-4 rounded-r-lg">
                        <h3 class="font-bold text-gray-800 mb-2">Oywon</h3>
                        <a href="mailto:20220654976oywon@juniv.edu" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-envelope"></i> 20220654976oywon@juniv.edu
                        </a>
                    </div>

                    <div class="border-l-4 border-purple-600 bg-purple-50 p-4 rounded-r-lg">
                        <h3 class="font-bold text-gray-800 mb-2">Ahad</h3>
                        <a href="mailto:20220654977ahad@juniv.edu" class="text-purple-600 hover:text-purple-800">
                            <i class="fas fa-envelope"></i> 20220654977ahad@juniv.edu
                        </a>
                    </div>

                    <div class="border-l-4 border-orange-600 bg-orange-50 p-4 rounded-r-lg">
                        <h3 class="font-bold text-gray-800 mb-2">Nusaiba</h3>
                        <a href="mailto:20220655000nusaiba@juniv.edu" class="text-orange-600 hover:text-orange-800">
                            <i class="fas fa-envelope"></i> 20220655000nusaiba@juniv.edu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
