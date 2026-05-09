<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' | JobYaari' : 'JobYaari - Latest Jobs, Admit Cards, Results & More' ?></title>
    <?php if (isset($metaDescription)): ?>
    <meta name="description" content="<?= escape($metaDescription) ?>">
    <?php endif; ?>
    <?php if (isset($metaKeywords)): ?>
    <meta name="keywords" content="<?= escape($metaKeywords) ?>">
    <?php endif; ?>
    <?php if (isset($canonicalUrl)): ?>
    <link rel="canonical" href="<?= escape($canonicalUrl) ?>">
    <?php endif; ?>
    <?php if (isset($ogData)): ?>
    <meta property="og:title" content="<?= escape($ogData['title'] ?? '') ?>">
    <meta property="og:image" content="<?= escape($ogData['image'] ?? '') ?>">
    <meta property="og:description" content="<?= escape($ogData['description'] ?? '') ?>">
    <meta property="og:type" content="article">
    <?php endif; ?>
</head>
<body>
    <a href="#main" class="skip-link">Skip to main content</a>
    <header class="site-header">
        <nav class="navbar">
            <div class="container navbar-inner">
                <a href="/" class="logo" aria-label="JobYaari Home">
                    <span class="logo-icon">JY</span>
                    <span class="logo-text">JobYaari</span>
                </a>
                <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox" aria-hidden="true">
                <label for="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </label>
                <ul class="nav-links">
                    <li><a href="/" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="/search.php?type=jobs">Jobs</a></li>
                    <li><a href="/index.php?filter=admit-card">Admit Card</a></li>
                    <li><a href="/index.php?filter=result">Result</a></li>
                    <li><a href="/#about">About</a></li>
                    <li><a href="/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Blogs</a></li>
                    <li><a href="/#contact">Contact</a></li>
                </ul>
                <a href="https://wa.me/919876543210" class="whatsapp-btn" aria-label="Follow us on WhatsApp" target="_blank" rel="noopener">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.386 0-4.592-.838-6.32-2.233l-.44-.363-3.096 1.037 1.037-3.096-.363-.44A9.958 9.958 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                </a>
            </div>
        </nav>
    </header>
    <main id="main">
