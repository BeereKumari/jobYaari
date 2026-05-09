<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: /index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT b.id, b.title, b.slug, b.short_description, b.content, b.image, b.created_at, b.meta_keywords, b.status, c.name as category_name, c.slug as category_slug
    FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.slug = ? AND b.status = 'published'");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Page Not Found';
    require_once __DIR__ . '/includes/header.php'; ?>
    <div class="container" style="padding: 60px 0; text-align: center;">
        <h1>404 - Blog Not Found</h1>
        <p>The blog post you are looking for does not exist.</p>
        <a href="/index.php" class="btn btn-primary">Back to Blogs</a>
    </div>
    <?php require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $blog['title'];
$metaDescription = $blog['short_description'];
$metaKeywords = $blog['meta_keywords'];
$canonicalUrl = 'https://jobyaari.com/blog-detail.php?slug=' . $blog['slug'];
$ogData = [
    'title' => $blog['title'],
    'description' => $blog['short_description'],
    'image' => $blog['image'] ? '/assets/uploads/blogs/' . $blog['image'] : '',
];

// Get categories for sidebar
$categories = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC")->fetchAll();
$recentPostsStmt = $pdo->prepare("SELECT title, slug, created_at FROM blogs WHERE status = 'published' AND slug != ? ORDER BY created_at DESC LIMIT 5");
$recentPostsStmt->execute([$slug]);
$recentPosts = $recentPostsStmt->fetchAll();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="page-banner">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a> &gt; <a href="/index.php">Blogs</a> &gt; <span><?= escape($blog['title']) ?></span>
        </nav>
    </div>
</div>

<div class="container blog-layout">
    <div class="blog-main">
        <article class="blog-detail">
            <?php if ($blog['image']): ?>
            <div class="blog-detail-image">
                <img src="/assets/uploads/blogs/<?= escape($blog['image']) ?>" alt="<?= escape($blog['title']) ?>">
            </div>
            <?php endif; ?>
            <div class="blog-detail-header">
                <a href="/index.php?filter=<?= escape($blog['category_slug']) ?>" class="category-badge"><?= escape($blog['category_name']) ?></a>
                <time class="blog-card-date"><?= formatDate($blog['created_at']) ?></time>
            </div>
            <h1 class="blog-detail-title"><?= escape($blog['title']) ?></h1>
            <div class="blog-detail-content">
                <?= htmlspecialchars_decode($blog['content'], ENT_QUOTES) ?>
            </div>
        </article>
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
        <div class="sidebar-section">
            <h3 class="sidebar-title">Recent Posts</h3>
            <?php foreach ($recentPosts as $post): ?>
            <div class="recent-post">
                <a href="/blog-detail.php?slug=<?= escape($post['slug']) ?>"><?= escape($post['title']) ?></a>
                <time><?= formatDate($post['created_at']) ?></time>
            </div>
            <?php endforeach; ?>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
