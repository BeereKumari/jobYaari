<?php
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/blogs/index.php');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request.');
    header('Location: /admin/blogs/index.php');
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /admin/blogs/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT image FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if ($blog) {
    if ($blog['image'] && file_exists(__DIR__ . '/../../assets/uploads/blogs/' . $blog['image'])) {
        unlink(__DIR__ . '/../../assets/uploads/blogs/' . $blog['image']);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $deleteStmt->execute([$id]);
}

setFlash('success', 'Blog deleted successfully.');
header('Location: /admin/blogs/index.php');
exit;
