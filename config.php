<?php
/**
 * DJANGO GIRLS SIERRA LEONE - COMMUNITY MANAGEMENT PLATFORM
 * Course: DCOMP207 Web Programming - Final Project
 * Student: Alpha Hamidu Jalloh (ID: 905005193, BIT2101)
 * Database Connection & Global System Configuration
 */

// 1. Initialize session securely if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false, // Set to true when running over HTTPS in production
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 2. Database Connection Credentials
$dbHost = '127.0.0.1';
$dbName = 'djangogirls_db';
$dbUser = 'root';
$dbPass = '';

// Support both standard XAMPP MySQL ports (3306 and 3308)
$portsToTry = [3306, 3308];
$pdo = null;
$dbError = null;

foreach ($portsToTry as $port) {
    try {
        $dsn = "mysql:host={$dbHost};port={$port};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        // If connection succeeds, keep this PDO instance and break
        break;
    } catch (PDOException $e) {
        $dbError = $e->getMessage();
    }
}

// If connection failed on both ports, attempt fallback without specifying dbName
if (!$pdo) {
    foreach ($portsToTry as $port) {
        try {
            $dsn = "mysql:host={$dbHost};port={$port};charset=utf8mb4";
            $tempPdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Auto-create database if it does not exist yet
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tempPdo->exec("USE `{$dbName}`");
            
            $pdo = new PDO("mysql:host={$dbHost};port={$port};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]);
            break;
        } catch (PDOException $e) {
            $dbError = $e->getMessage();
        }
    }
}

// Global constant definition
define('SITE_NAME', 'Django Girls Sierra Leone');
define('APP_VERSION', '2.0.0 Final');
define('STUDENT_NAME', 'Group 7');
define('STUDENT_ID', '');
define('COURSE_CODE', 'DCOMP207');

// 3. Global Helper Functions

/**
 * Sanitize and escape HTML output to prevent XSS attacks
 */
function escape($data) {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if the user is authenticated
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Retrieve current logged-in user ID
 */
function current_user_id() {
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Fetch full profile of current logged-in user
 */
function current_user($pdo) {
    if (!is_logged_in() || !$pdo) {
        return null;
    }
    
    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $cachedUser = $stmt->fetch();
        return $cachedUser ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if current user is an Administrator
 */
function is_admin($pdo) {
    $user = current_user($pdo);
    return ($user && $user['role'] === 'admin');
}

/**
 * Check if current user is a Mentor
 */
function is_mentor($pdo) {
    $user = current_user($pdo);
    return ($user && ($user['role'] === 'mentor' || $user['role'] === 'admin'));
}

/**
 * Require authentication - redirect to login if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to access this page.';
        header('Location: login.php');
        exit;
    }
}

/**
 * Require administrator privileges
 */
function require_admin($pdo) {
    require_login();
    if (!is_admin($pdo)) {
        $_SESSION['flash_error'] = 'Access denied: Administrator privileges required.';
        header('Location: ../dashboard.php');
        exit;
    }
}

/**
 * Safe HTTP redirection
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Set flash messages in session
 */
function set_flash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash($type) {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

/**
 * Friendly date formatter
 */
function format_date($datetime, $format = 'M d, Y h:i A') {
    if (!$datetime) return 'N/A';
    $time = strtotime($datetime);
    return $time ? date($format, $time) : 'N/A';
}
