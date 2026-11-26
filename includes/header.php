<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config.php';
}

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' - Sierra Leone Community Platform';
$currentUser = current_user($pdo);
$basePath = (isset($inAdmin) && $inAdmin) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?></title>
    <!-- Use Tahoma font with size 12/standard per project brief instructions -->
    <link rel="stylesheet" href="<?= $basePath ?>style.css">
    <link rel="icon" type="image/png" href="<?= $basePath ?>assets/jgirls.png">
</head>
<body>

<header class="navbar">
    <div class="navbar-container">
        <a href="<?= $basePath ?>index.php" class="brand">
            <img src="<?= $basePath ?>assets/jgirls.png" alt="Django Girls Logo" class="brand-logo" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/a/a6/Djangogirls-logo.png'">
            <span class="brand-text">Sierra Leone</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-menu" id="navMenu">
            <a href="<?= $basePath ?>index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a>
            <a href="<?= $basePath ?>events.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'active' : '' ?>">Workshops</a>
            <a href="<?= $basePath ?>resources.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'resources.php') ? 'active' : '' ?>">Resources</a>
            <a href="<?= $basePath ?>community.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'community.php' || basename($_SERVER['PHP_SELF']) == 'thread.php') ? 'active' : '' ?>">Forum</a>
            <a href="<?= $basePath ?>mentorship.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'mentorship.php') ? 'active' : '' ?>">Mentorship</a>

            <?php if (is_logged_in() && $currentUser): ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>admin/index.php" class="nav-link admin-pill <?= (isset($inAdmin) && $inAdmin) ? 'active' : '' ?>">&#9881; Admin Panel</a>
                <?php endif; ?>
                
                <a href="<?= $basePath ?>dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">Dashboard</a>
                
                <div class="user-menu">
                    <a href="<?= $basePath ?>profile.php" class="user-profile-link" title="View Profile">
                        <img src="<?= $basePath . escape($currentUser['profile_picture'] ?? 'assets/default_avatar.svg') ?>" alt="Avatar" class="nav-avatar" onerror="this.src='<?= $basePath ?>assets/default_avatar.svg'">
                        <span class="user-name"><?= escape(explode(' ', $currentUser['full_name'])[0]) ?></span>
                    </a>
                    <a href="<?= $basePath ?>logout.php" class="button button-small button-outline">Logout</a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?= $basePath ?>login.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : '' ?>">Login</a>
                    <a href="<?= $basePath ?>register.php" class="button button-small">Sign Up</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="flash-wrapper">
    <?php if ($msg = get_flash('success')): ?>
        <div class="alert alert-success">
            <span class="alert-icon">&#10003;</span>
            <span><?= escape($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = get_flash('error')): ?>
        <div class="alert alert-error">
            <span class="alert-icon">&#9888;</span>
            <span><?= escape($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = get_flash('info')): ?>
        <div class="alert alert-info">
            <span class="alert-icon">&#8505;</span>
            <span><?= escape($msg) ?></span>
        </div>
    <?php endif; ?>
</div>
