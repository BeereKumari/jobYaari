<?php
require_once dirname(__DIR__, 1) . '/includes/db.php';
require_once dirname(__DIR__, 1) . '/includes/auth.php';
require_once dirname(__DIR__, 1) . '/includes/functions.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="/assets/css/editor.css">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' | Admin | JobYaari' : 'Dashboard | Admin | JobYaari' ?></title>
    <script src="/assets/js/editor.js"></script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="/admin/index.php" class="admin-logo">
                <span class="logo-icon">JY</span>
                <span class="logo-text">Jobs Yaari</span>
            </a>
            <div class="admin-status">
                <span class="status-dot"></span>
                <span class="status-text">Online</span>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li>
                        <a href="/admin/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/blogs') === false && strpos($_SERVER['PHP_SELF'], '/admin/categories') === false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/blogs/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/blogs') !== false ? 'active' : '' ?>">
                            <span class="nav-icon">&#9776;</span>
                            <span class="nav-label">Manage Blog</span>
                        </a>
                        <ul class="admin-subnav <?= strpos($_SERVER['PHP_SELF'], '/admin/blogs') !== false ? 'open' : '' ?>">
                            <li><a href="/admin/blogs/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/blogs') !== false ? 'active' : '' ?>">Blogs</a></li>
                            <li><a href="/admin/categories/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/categories') !== false ? 'active' : '' ?>">Categories</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="/admin/profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                            <span class="nav-icon">&#9881;</span>
                            <span class="nav-label">Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="/logout.php">
                            <span class="nav-icon">&#10140;</span>
                            <span class="nav-label">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <div class="breadcrumb">
                    <?php if (isset($breadcrumb)): ?>
                        <?= $breadcrumb ?>
                    <?php else: ?>
                        <a href="/admin/index.php">Home</a> &bull; Dashboard
                    <?php endif; ?>
                </div>
                <div class="admin-topbar-actions">
                    <span class="admin-user"><?= escape($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </header>
            <div class="admin-content">
