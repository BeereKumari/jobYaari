<?php
$pageTitle = 'Edit Category';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; <a href="/admin/categories/index.php">Categories</a> &bull; Edit';
require_once dirname(__DIR__, 2) . '/includes/admin-header.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /admin/categories/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlash('error', 'Category not found.');
    header('Location: /admin/categories/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
        header('Location: /admin/categories/index.php');
        exit;
    }

    $name = sanitize($_POST['name'] ?? '');
    $slug = generateSlug($name);

    if (empty($name)) {
        setFlash('error', 'Category name is required.');
    } else {
        $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $id]);
        if ($checkStmt->fetch()) {
            setFlash('error', 'A category with this name already exists.');
        } else {
            $updateStmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
            $updateStmt->execute([$name, $slug, $id]);
            setFlash('success', 'Category updated successfully.');
            header('Location: /admin/categories/index.php');
            exit;
        }
    }
}

$csrfToken = generateCSRFToken();
$flash = getFlash();
?>

<?php if ($flash): ?>
<div class="alert alert-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Edit Category</h2></div>
    <form method="POST" action="/admin/categories/edit.php?id=<?= $id ?>" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
        <div class="form-group">
            <label for="name">Category Name *</label>
            <input type="text" id="name" name="name" required maxlength="100" value="<?= escape($category['name']) ?>">
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input type="text" value="<?= escape($category['slug']) ?>" disabled class="input-disabled">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="/admin/categories/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
