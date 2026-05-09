<?php
require_once dirname(__DIR__, 1) . '/includes/db.php';
require_once dirname(__DIR__, 1) . '/includes/functions.php';

header('Content-Type: text/html; charset=utf-8');

$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$date = isset($_GET['date']) ? sanitize($_GET['date']) : '';

$where = "b.status = 'published'";
$params = [];

if ($category) {
    $where .= " AND c.slug = ?";
    $params[] = $category;
}
if ($date) {
    $where .= " AND DATE_FORMAT(b.created_at, '%Y-%m') = ?";
    $params[] = $date;
}

$sql = "SELECT b.id, b.title, b.slug, b.short_description, b.image, b.created_at, c.name as category_name, c.slug as category_slug
        FROM blogs b LEFT JOIN categories c ON b.category_id = c.id
        WHERE $where ORDER BY b.created_at DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();

if (empty($blogs)): ?>
    <div class="no-results">
        <p>No blogs found. Try changing your filters.</p>
    </div>
<?php else:
    foreach ($blogs as $blog): ?>
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
    <?php endforeach;
endif;
