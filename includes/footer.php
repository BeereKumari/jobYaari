    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>JobYaari</h3>
                    <p>Your trusted source for latest government jobs, admit cards, results, and educational updates.</p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/index.php">Blogs</a></li>
                        <li><a href="/search.php?type=jobs">Latest Jobs</a></li>
                        <li><a href="/#about">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <?php
                        if (!isset($pdo)) require_once __DIR__ . '/db.php';
                        $footerCats = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC LIMIT 5")->fetchAll();
                        foreach ($footerCats as $fc): ?>
                        <li><a href="/index.php?filter=<?= escape($fc['slug']) ?>"><?= escape($fc['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Connect</h4>
                    <ul>
                        <li><a href="/#contact">Contact Us</a></li>
                        <li><a href="https://wa.me/919876543210" target="_blank" rel="noopener">WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> JobYaari. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/assets/js/filter.js"></script>
</body>
</html>
