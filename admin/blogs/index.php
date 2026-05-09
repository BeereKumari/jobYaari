<?php
$pageTitle = 'Manage Blogs';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; <a href="/admin/blogs/index.php">Blog</a> &bull; Manage Blogs';
require_once dirname(__DIR__, 2) . '/includes/admin-header.php';

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where = "1=1";
$params = [];
if ($search) {
    $where .= " AND (b.title LIKE ? OR c.name LIKE ?)";
    $likeTerm = '%' . $search . '%';
    $params[] = $likeTerm;
    $params[] = $likeTerm;
}

$countSql = "SELECT COUNT(*) FROM blogs b LEFT JOIN categories c ON b.category_id = c.id WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBlogs = $countStmt->fetchColumn();
$totalPages = ceil($totalBlogs / $perPage);

$sql = "SELECT b.id, b.title, b.slug, b.status, b.image, b.created_at, c.name as category_name
        FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
        WHERE $where ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$blogs = $stmt->fetchAll();

$flash = getFlash();
?>

<?php if ($flash): ?>
<div class="alert alert-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>All Blogs</h2>
        <a href="/admin/blogs/add.php" class="btn btn-primary btn-sm">Add New Blog</a>
    </div>
    <div class="filter-bar admin-filter">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="filter-input" placeholder="Search blogs..." value="<?= escape($search) ?>">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Thumbnail</th>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $blog): ?>
                <tr>
                    <td><?= $blog['id'] ?></td>
                    <td>
                        <?php if ($blog['image']): ?>
                        <img src="/assets/uploads/blogs/<?= escape($blog['image']) ?>" alt="" class="table-thumb">
                        <?php else: ?>
                        <span class="no-thumb">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= escape(truncate($blog['title'], 50)) ?></td>
                    <td><?= escape($blog['category_name']) ?></td>
                    <td><span class="status-badge status-<?= $blog['status'] ?>"><?= ucfirst($blog['status']) ?></span></td>
                    <td><?= formatDate($blog['created_at']) ?></td>
                    <td class="table-actions">
                        <a href="/admin/blogs/edit.php?id=<?= $blog['id'] ?>" class="action-edit">Edit</a>
                        <form method="POST" action="/admin/blogs/delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this blog?');">
                            <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="action-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($blogs)): ?>
                <tr><td colspan="7" class="no-results">No blogs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn">&laquo; Prev</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn">Next &raquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
