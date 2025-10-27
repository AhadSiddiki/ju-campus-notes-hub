<?php
require_once '../config/init.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
