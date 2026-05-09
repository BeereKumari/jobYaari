<?php
session_start();

define('SESSION_TIMEOUT', 1800); // 30 minutes

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /login.php');
        exit;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: /login.php?expired=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

function isLoggedIn(): bool {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}
