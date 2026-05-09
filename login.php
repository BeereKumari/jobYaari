<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$expired = isset($_GET['expired']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = ['count' => 0, 'time' => 0];
        }

        if ($_SESSION['login_attempts']['count'] >= 5 && (time() - $_SESSION['login_attempts']['time']) < 300) {
            $error = 'Too many failed attempts. Please try again after 5 minutes.';
        } elseif (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                unset($_SESSION['login_attempts']);
                header('Location: /admin/index.php');
                exit;
            } else {
                $_SESSION['login_attempts']['count']++;
                $_SESSION['login_attempts']['time'] = time();
                $error = 'Invalid username or password.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Admin Login | JobYaari</title>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <span class="logo-icon">JY</span>
                <span class="logo-text">JobYaari</span>
            </div>
            <h1 class="login-title">Admin Login</h1>
            <?php if ($expired): ?>
            <div class="alert alert-warning">Session expired. Please login again.</div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= escape($error) ?></div>
            <?php endif; ?>
            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
            <?php endif; ?>
            <form method="POST" action="/login.php" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required minlength="3" value="<?= escape($username ?? '') ?>" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
