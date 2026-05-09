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

$name = sanitize($_POST['name'] ?? '');
$slug = generateSlug($name);

if (empty($name)) {
    setFlash('error', 'Category name is required.');
    header('Location: /admin/categories/index.php');
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
$checkStmt->execute([$slug]);
if ($checkStmt->fetch()) {
    setFlash('error', 'A category with this name already exists.');
} else {
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->execute([$name, $slug]);
    setFlash('success', 'Category added successfully.');
}

header('Location: /admin/categories/index.php');
exit;
