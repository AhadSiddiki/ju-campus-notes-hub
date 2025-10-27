<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_API_KEY');
define('GOOGLE_CLIENT_SECRET', 'YOUR_API_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/campus-notes-hub/auth/google-callback.php');

// OAuth endpoints
define('GOOGLE_OAUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Required email domain
define('ALLOWED_EMAIL_DOMAIN', '@juniv.edu');
?>
