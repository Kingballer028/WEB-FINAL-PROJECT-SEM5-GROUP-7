<?php
/**
 * DJANGO GIRLS SIERRA LEONE - COMMUNITY MANAGEMENT PLATFORM
 * Secure Logout Handler
 */
require_once 'config.php';

// Unset all session variables
$_SESSION = [];

// Clear the session cookie from client browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session entirely
session_destroy();

// Start fresh session to pass the logged_out flash feedback
session_start();
set_flash('info', 'You have been logged out securely. See you again soon!');

redirect('login.php');
