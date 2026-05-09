<?php
$pageTitle = 'Dashboard';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; Dashboard';
require_once dirname(__DIR__, 1) . '/includes/admin-header.php';

// Stats
$totalBlogs = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$publishedBlogs = $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
$draftBlogs = $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'draft'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Recent blogs
$recentBlogs = $pdo->query("SELECT b.id, b.title, b.status, b.created_at, c.name as category_name
    FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
    ORDER BY b.created_at DESC LIMIT 10")->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">&#9776;</div>
        <div class="stat-info">
            <span class="stat-count"><?= $totalBlogs ?></span>
            <span class="stat-label">Total Blogs</span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">&#10003;</div>
        <div class="stat-info">
            <span class="stat-count"><?= $publishedBlogs ?></span>
            <span class="stat-label">Published</span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">&#9888;</div>
        <div class="stat-info">
            <span class="stat-count"><?= $draftBlogs ?></span>
            <span class="stat-label">Draft</span>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">&#9632;</div>
        <div class="stat-info">
            <span class="stat-count"><?= $totalCategories ?></span>
            <span class="stat-label">Categories</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Blogs</h2>
        <a href="/admin/blogs/index.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentBlogs as $index => $blog): ?>
                <tr class="<?= $index % 2 === 0 ? '' : 'row-alt' ?>">
                    <td><?= $blog['id'] ?></td>
                    <td><?= escape($blog['title']) ?></td>
                    <td><?= escape($blog['category_name']) ?></td>
                    <td><span class="status-badge status-<?= $blog['status'] ?>"><?= ucfirst($blog['status']) ?></span></td>
                    <td><?= formatDate($blog['created_at']) ?></td>
                    <td class="table-actions">
                        <a href="/admin/blogs/edit.php?id=<?= $blog['id'] ?>" class="action-edit">Edit</a>
                        <form method="POST" action="/admin/blogs/delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this blog?');">
                            <input type="hidden" name="id" value="<?= $blog['id'] ?>">
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

<?php require_once dirname(__DIR__, 1) . '/includes/admin-footer.php'; ?>
