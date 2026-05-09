<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Search Results';
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$perPage = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$where = "b.status = 'published'";
$params = [];

if ($search) {
    $where .= " AND (b.title LIKE ? OR b.content LIKE ? OR b.short_description LIKE ?)";
    $likeTerm = '%' . $search . '%';
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $pageTitle = 'Search: ' . $search;
}

if ($type) {
    $where .= " AND c.slug = ?";
    $params[] = $type;
}

$countSql = "SELECT COUNT(*) FROM blogs b LEFT JOIN categories c ON b.category_id = c.id WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBlogs = $countStmt->fetchColumn();
$totalPages = ceil($totalBlogs / $perPage);

$sql = "SELECT b.id, b.title, b.slug, b.short_description, b.image, b.created_at, c.name as category_name, c.slug as category_slug
        FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
        WHERE $where ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$blogs = $stmt->fetchAll();

$categories = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC")->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="page-banner">
    <div class="container">
        <h1><?= $search ? 'Search Results' : 'Jobs' ?></h1>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a> &gt; <span><?= $search ? 'Search Results' : 'Jobs' ?></span>
        </nav>
    </div>
</div>

<div class="container blog-layout">
    <div class="blog-main">
        <?php if ($search): ?>
        <p class="search-info"><?= $totalBlogs ?> result(s) found for "<strong><?= escape($search) ?></strong>"</p>
        <?php endif; ?>

        <?php if (empty($blogs)): ?>
        <div class="no-results">
            <h2>No results found</h2>
            <p>Try adjusting your search terms or browse our categories.</p>
        </div>
        <?php else: ?>
            <?php foreach ($blogs as $blog): ?>
            <article class="blog-card">
                <a href="/blog-detail.php?slug=<?= escape($blog['slug']) ?>" class="blog-card-thumb">
                    <img src="/assets/uploads/blogs/<?= escape($blog['image']) ?>" alt="<?= escape($blog['title']) ?>" loading="lazy" onerror="this.src='/assets/images/placeholder.svg'">
                </a>
                <div class="blog-card-content">
                    <a href="/index.php?filter=<?= escape($blog['category_slug']) ?>" class="category-badge"><?= escape($blog['category_name']) ?></a>
                    <h2 class="blog-card-title">
                        <a href="/blog-detail.php?slug=<?= escape($blog['slug']) ?>"><?= escape($blog['title']) ?></a>
                    </h2>
                    <p class="blog-card-desc"><?= escape(truncate($blog['short_description'], 150)) ?></p>
                    <div class="blog-card-footer">
                        <a href="/blog-detail.php?slug=<?= escape($blog['slug']) ?>" class="btn btn-read-more">Read More</a>
                        <time class="blog-card-date"><?= formatDate($blog['created_at']) ?></time>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $type ? '&type=' . urlencode($type) : '' ?>" class="pagination-btn">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $type ? '&type=' . urlencode($type) : '' ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $type ? '&type=' . urlencode($type) : '' ?>" class="pagination-btn">Next &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>

    <aside class="blog-sidebar">
        <div class="sidebar-section">
            <h3 class="sidebar-title">Categories</h3>
            <div class="category-tags">
                <?php foreach ($categories as $cat): ?>
                <a href="/index.php?filter=<?= escape($cat['slug']) ?>" class="category-tag"><?= escape($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
