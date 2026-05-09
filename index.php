<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Blogs';
$perPage = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Get filter params
$filterCategory = isset($_GET['filter']) ? sanitize($_GET['filter']) : '';
$filterDate = isset($_GET['date']) ? sanitize($_GET['date']) : '';

// Build query
$where = "b.status = 'published'";
$params = [];

if ($filterCategory) {
    $where .= " AND c.slug = ?";
    $params[] = $filterCategory;
}
if ($filterDate) {
    $where .= " AND DATE_FORMAT(b.created_at, '%Y-%m') = ?";
    $params[] = $filterDate;
}

// Count total
$countSql = "SELECT COUNT(*) FROM blogs b LEFT JOIN categories c ON b.category_id = c.id WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBlogs = $countStmt->fetchColumn();
$totalPages = ceil($totalBlogs / $perPage);

// Fetch blogs
$sql = "SELECT b.id, b.title, b.slug, b.short_description, b.image, b.created_at, c.name as category_name, c.slug as category_slug
        FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
        WHERE $where ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$blogs = $stmt->fetchAll();

// Get all categories for sidebar
$categories = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC")->fetchAll();

// Get dates for filter
$dates = $pdo->query("SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month FROM blogs ORDER BY created_at DESC")->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="page-banner">
    <div class="container">
        <h1>Blogs</h1>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a> &gt; <span>Blogs</span>
        </nav>
    </div>
</div>

<div class="container blog-layout">
    <div class="blog-main">
        <div class="filter-bar">
            <div class="filter-group">
                <label for="filter-category" class="sr-only">Filter by Category</label>
                <select id="filter-category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= escape($cat['slug']) ?>" <?= $filterCategory === $cat['slug'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-date" class="sr-only">Filter by Month</label>
                <select id="filter-date" class="filter-select">
                    <option value="">All Months</option>
                    <?php foreach ($dates as $d):
                        $label = date('F Y', strtotime($d['month'] . '-01'));
                    ?>
                    <option value="<?= escape($d['month']) ?>" <?= $filterDate === $d['month'] ? 'selected' : '' ?>><?= escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button id="btn-filter" class="btn btn-primary">Filter</button>
            <button id="btn-clear" class="btn btn-secondary">Clear</button>
        </div>

        <div id="loading-spinner" class="spinner" style="display:none;"></div>
        <div id="blog-list">
            <?php if (empty($blogs)): ?>
            <div class="no-results">
                <p>No blogs found. Try changing your filters.</p>
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
            </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $filterCategory ? '&filter=' . urlencode($filterCategory) : '' ?><?= $filterDate ? '&date=' . urlencode($filterDate) : '' ?>" class="pagination-btn">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= $filterCategory ? '&filter=' . urlencode($filterCategory) : '' ?><?= $filterDate ? '&date=' . urlencode($filterDate) : '' ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $filterCategory ? '&filter=' . urlencode($filterCategory) : '' ?><?= $filterDate ? '&date=' . urlencode($filterDate) : '' ?>" class="pagination-btn">Next &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>

    <aside class="blog-sidebar">
        <div class="whatsapp-card">
            <div class="whatsapp-card-content">
                <h3>50K+ followers</h3>
                <p>Get daily job updates on WhatsApp</p>
                <a href="https://wa.me/919876543210" class="btn btn-whatsapp" target="_blank" rel="noopener">Follow</a>
            </div>
        </div>
        <div class="sidebar-section">
            <h3 class="sidebar-title">Categories</h3>
            <div class="category-tags">
                <?php foreach ($categories as $cat): ?>
                <a href="#" class="category-tag <?= $filterCategory === $cat['slug'] ? 'active' : '' ?>" data-category="<?= escape($cat['slug']) ?>"><?= escape($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="sidebar-section">
            <h3 class="sidebar-title">Recent Posts</h3>
            <?php
            $recentPosts = $pdo->query("SELECT b.title, b.slug, b.created_at FROM blogs b WHERE b.status = 'published' ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
            foreach ($recentPosts as $post): ?>
            <div class="recent-post">
                <a href="/blog-detail.php?slug=<?= escape($post['slug']) ?>"><?= escape($post['title']) ?></a>
                <time><?= formatDate($post['created_at']) ?></time>
            </div>
            <?php endforeach; ?>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
