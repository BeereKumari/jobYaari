<?php
$pageTitle = 'Manage Categories';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; <a href="/admin/blogs/index.php">Blog</a> &bull; Categories';
require_once dirname(__DIR__, 2) . '/includes/admin-header.php';

$categories = $pdo->query("SELECT c.id, c.name, c.slug, c.created_at, COUNT(b.id) as blog_count
    FROM categories c LEFT JOIN blogs b ON c.id = b.category_id
    GROUP BY c.id ORDER BY c.name ASC")->fetchAll();

$csrfToken = generateCSRFToken();
$flash = getFlash();
?>

<?php if ($flash): ?>
<div class="alert alert-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Add Category</h2></div>
    <form method="POST" action="/admin/categories/add.php" class="admin-form admin-form-inline">
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
        <div class="form-group form-group-inline">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required maxlength="100" placeholder="Category name">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h2>All Categories</h2></div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Blog Count</th>
                    <th scope="col">Created</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= escape($cat['name']) ?></td>
                    <td><?= escape($cat['slug']) ?></td>
                    <td><?= $cat['blog_count'] ?></td>
                    <td><?= formatDate($cat['created_at']) ?></td>
                    <td class="table-actions">
                        <a href="/admin/categories/edit.php?id=<?= $cat['id'] ?>" class="action-edit">Edit</a>
                        <form method="POST" action="/admin/categories/delete.php" style="display:inline;" onsubmit="return confirm('Delete this category? Cannot delete if blogs are assigned.');">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                            <button type="submit" class="action-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
