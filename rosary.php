<?php

declare(strict_types=1);

$rosary = require __DIR__ . '/data/rosary.php';
$manilaNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
$todayMystery = $rosary['schedule'][(int) $manilaNow->format('N')];
$rosaryJson = json_encode(
    ['date' => $manilaNow->format('Y-m-d'), 'today' => $todayMystery, 'prayers' => $rosary['prayers'], 'mysteries' => $rosary['mysteries']],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="Pray the Holy Rosary in English or Cebuano with the mysteries selected for today.">
    <title>Holy Rosary — Kristohanon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=18">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Kristohanon home">
                <svg class="brand-mark" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1.8c.55 6.55 3.65 9.65 10.2 10.2-6.55.55-9.65 3.65-10.2 10.2C11.45 15.65 8.35 12.55 1.8 12 8.35 11.45 11.45 8.35 12 1.8Z"/></svg>
                <span>Kristohanon</span>
            </a>
            <nav class="site-nav" aria-label="Main navigation">
                <a class="nav-link" href="/">Home</a>
                <a class="nav-link is-active" href="rosary.php" aria-current="page">Rosary</a>
                <a class="nav-link" href="prayers.php">Prayers</a>
            </nav>
        </header>

        <main class="rosary-main" id="mainContent">
            <section class="rosary-hero" aria-labelledby="rosaryTitle">
                <p class="eyebrow" id="rosaryEyebrow">A guided prayer, one bead at a time</p>
                <h1 id="rosaryTitle">The Holy Rosary</h1>
                <p class="lede" id="rosaryLede">Today’s mysteries are chosen automatically for <?= htmlspecialchars($manilaNow->format('l'), ENT_QUOTES, 'UTF-8') ?>. You may select another set at any time.</p>
            </section>

            <section class="rosary-controls" aria-label="Rosary settings">
                <div class="language-switch rosary-language" role="group" aria-label="Prayer language">
                    <button type="button" class="language-option is-active" data-rosary-language="en" aria-pressed="true">English</button>
                    <button type="button" class="language-option" data-rosary-language="ceb" aria-pressed="false">Cebuano</button>
                </div>
                <div class="mystery-picker" role="group" aria-label="Mystery set">
                    <?php foreach (['joyful', 'sorrowful', 'glorious', 'luminous'] as $mystery): ?>
                        <button type="button" class="mystery-option<?= $mystery === $todayMystery ? ' is-active' : '' ?>" data-mystery="<?= $mystery ?>" aria-pressed="<?= $mystery === $todayMystery ? 'true' : 'false' ?>">
                            <?= ucfirst($mystery) ?>
                            <?php if ($mystery === $todayMystery): ?><span class="today-chip">Today</span><?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="rosary-guide" aria-live="polite">
                <div class="rosary-progress-row">
                    <div>
                        <p class="step-kicker" id="stepKicker">Opening prayers</p>
                        <p class="step-count" id="stepCount">Step 1 of 81</p>
                    </div>
                    <div class="progress-track" aria-hidden="true"><span id="progressBar"></span></div>
                </div>

                <div class="mystery-context" id="mysteryContext" hidden>
                    <span class="mystery-number" id="mysteryNumber"></span>
                    <div><strong id="mysteryName"></strong><span id="mysteryReference"></span></div>
                </div>

                <article class="prayer-card">
                    <p class="bead-label" id="beadLabel"></p>
                    <h2 id="prayerTitle">The Sign of the Cross</h2>
                    <p class="prayer-text" id="prayerText"></p>
                </article>

                <div class="decade-beads" id="decadeBeads" aria-label="Decade progress" hidden></div>

                <div class="rosary-actions">
                    <button class="secondary-button" id="previousPrayer" type="button">← Previous</button>
                    <button class="send-button" id="nextPrayer" type="button"><span>Begin</span><span aria-hidden="true">→</span></button>
                </div>
                <button class="reset-button" id="resetRosary" type="button">Start over</button>
            </section>

            <section class="mystery-overview" aria-labelledby="overviewTitle">
                <p class="eyebrow" id="overviewEyebrow">Today’s meditations</p>
                <h2 id="overviewTitle"></h2>
                <ol id="mysteryList"></ol>
            </section>

            <p class="source-note" id="sourceNote">The mystery schedule and English prayer order follow the Holy See and USCCB. Cebuano prayer wording follows the Basilica Minore del Sto. Niño de Cebu.</p>
        </main>

        <footer><span>Made to help people pause, pray, and return to Scripture.</span></footer>
    </div>

    <script>window.ROSARY_DATA = <?= $rosaryJson ?>;</script>
    <script src="assets/rosary.js?v=1" defer></script>
</body>
</html>
