<?php
declare(strict_types=1);
$prayers = require __DIR__ . '/data/prayers.php';
$prayersJson = json_encode($prayers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="A searchable collection of essential Catholic prayers in English and Cebuano.">
    <title>Catholic Prayers — Still Waters</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=15">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Still Waters home"><span class="brand-mark" aria-hidden="true">✦</span><span>Still Waters</span></a>
            <nav class="site-nav" aria-label="Main navigation">
                <a class="nav-link" href="/">Home</a>
                <a class="nav-link" href="rosary.php">Rosary</a>
                <a class="nav-link is-active" href="prayers.php" aria-current="page">Prayers</a>
            </nav>
        </header>
        <main class="prayers-main" id="mainContent">
            <section class="prayers-hero" aria-labelledby="prayersTitle">
                <p class="eyebrow">Words the Church has carried for generations</p>
                <h1 id="prayersTitle">Prayers to keep close</h1>
                <p class="lede">For ordinary mornings, difficult decisions, protection, forgiveness, and remembrance—find the words when your own are hard to form.</p>
            </section>
            <section class="prayer-tools" aria-label="Prayer library controls">
                <div class="language-switch prayer-language" role="group" aria-label="Prayer language">
                    <button type="button" class="language-option is-active" data-prayer-language="en" aria-pressed="true">English</button>
                    <button type="button" class="language-option" data-prayer-language="ceb" aria-pressed="false">Cebuano</button>
                </div>
                <label class="prayer-search"><span class="sr-only">Search prayers</span><span aria-hidden="true">⌕</span><input id="prayerSearch" type="search" placeholder="Search by prayer or need…" autocomplete="off"></label>
                <div class="prayer-categories" id="prayerCategories" aria-label="Prayer categories"></div>
            </section>
            <div class="library-summary" aria-live="polite"><p id="prayerCount"></p><button type="button" id="expandPrayers">Open all</button></div>
            <section class="prayer-library" id="prayerLibrary" aria-label="Catholic prayer library"></section>
            <p class="empty-library" id="emptyLibrary" hidden>No prayers match that search. Try a simpler word such as “protection,” “Mary,” or “forgiveness.”</p>
            <p class="source-note">Traditional English texts follow the USCCB’s collection of basic Catholic prayers. Verified Cebuano Rosary and Angelus wording follows the Basilica Minore del Sto. Niño de Cebu; other Cebuano texts are provided as devotional translations.</p>
        </main>
        <footer><span>Made to help people pause, pray, and return to Scripture.</span></footer>
    </div>
    <script>window.PRAYERS_DATA = <?= $prayersJson ?>;</script>
    <script src="assets/prayers.js?v=2" defer></script>
</body>
</html>
