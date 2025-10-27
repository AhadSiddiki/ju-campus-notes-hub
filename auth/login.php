<?php
require_once '../config/init.php';
require_once '../includes/google-oauth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../dashboard/user-dashboard.php');
    exit();
}

// Note: All authentication is handled through Google OAuth
// No password authentication needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-emerald-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-emerald-900 mb-2">
                    <i class="fas fa-graduation-cap"></i> JU Campus Notes Hub
                </h1>
                <p class="text-gray-600">Welcome back! Please login to continue</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-6">
                    <div class="inline-block p-3 bg-emerald-100 rounded-full mb-3">
                        <i class="fas fa-sign-in-alt text-4xl text-emerald-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back!</h2>
                    <p class="text-gray-600">Sign in with your JU Google account</p>
                </div>

                <!-- Google Sign-In Button -->
                <a href="<?php echo getGoogleLoginUrl(); ?>" 
                   class="w-full flex items-center justify-center gap-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold py-4 px-6 rounded-xl hover:bg-gray-50 hover:border-emerald-500 hover:shadow-lg transition duration-200 mb-6">
                    <svg class="w-7 h-7" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="text-lg">Sign in with Google (@juniv.edu)</span>
                </a>

                <!-- Info Box -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-emerald-800 text-center">
                        <i class="fas fa-shield-alt"></i> 
                        Secure authentication powered by Google
                    </p>
                </div>

                <!-- Footer Links -->
                <div class="space-y-3 text-center">
                    <p class="text-gray-600">
                        Don't have an account? 
                        <a href="register.php" class="text-emerald-600 hover:text-emerald-800 font-semibold">
                            <i class="fas fa-user-plus"></i> Sign up here
                        </a>
                    </p>
                    <a href="../index.php" class="block text-gray-500 hover:text-gray-700">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
