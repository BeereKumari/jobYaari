<?php
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/categories/index.php');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request.');
    header('Location: /admin/categories/index.php');
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /admin/categories/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Category deleted successfully.');
} catch (PDOException $e) {
    setFlash('error', 'Cannot delete category. It may have blogs assigned to it.');
}

header('Location: /admin/categories/index.php');
exit;
