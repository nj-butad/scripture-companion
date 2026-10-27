<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(24));
}

$csrf = htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8');
$passages = require __DIR__ . '/data/passages.php';
$englishPassages = array_values(array_filter($passages, static fn (array $passage): bool => $passage['language'] === 'en'));
$cebuanoPassages = array_values(array_filter($passages, static fn (array $passage): bool => $passage['language'] === 'ceb'));
$dailyCandidates = array_values(array_filter(
    array_keys($englishPassages),
    static fn (int $index): bool => isset($cebuanoPassages[$index])
        && mb_strlen($englishPassages[$index]['text']) <= 650
        && mb_strlen($cebuanoPassages[$index]['text']) <= 650
));
$manilaNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
$dailyIndex = $dailyCandidates[(int) $manilaNow->format('z') % count($dailyCandidates)];
$dailyVerses = ['en' => $englishPassages[$dailyIndex], 'ceb' => $cebuanoPassages[$dailyIndex]];
$dailyJson = json_encode($dailyVerses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="A gentle, bilingual Scripture companion for difficult moments.">
    <title>Still Waters — Scripture Companion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=8">
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Still Waters home">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>Still Waters</span>
            </a>
            <nav class="site-nav" aria-label="Main navigation">
                <a class="nav-link is-active" href="/" aria-current="page">Home</a>
                <a class="nav-link" href="rosary.php">Rosary</a>
                <a class="nav-link" href="prayers.php">Prayers</a>
                <button class="quiet-button nav-link" id="aboutButton" type="button" aria-haspopup="dialog">About</button>
            </nav>
        </header>

        <main>
            <section class="intro" aria-labelledby="pageTitle">
                <p class="eyebrow">A quiet place to bring what you carry</p>
                <h1 id="pageTitle">You don’t have to find the right words.</h1>
                <p class="lede">Share what is on your heart. We’ll meet you with Scripture, a short reflection, and a simple prayer.</p>
            </section>

            <div class="companion-home" id="companionHome">
                <section class="companion" id="scriptureCompanion" aria-label="Scripture companion">
                    <div class="companion-topbar">
                        <div class="language-switch" role="group" aria-label="Conversation language">
                            <button type="button" class="language-option is-active" data-language="en" aria-pressed="true">English</button>
                            <button type="button" class="language-option" data-language="ceb" aria-pressed="false">Cebuano</button>
                        </div>
                        <button class="widget-close" id="closeChatWidget" type="button" aria-label="Return chat to the page">×</button>
                    </div>

                    <div class="conversation" id="conversation" aria-live="polite">
                        <article class="welcome-card">
                            <span class="small-cross" aria-hidden="true">✦</span>
                            <p id="welcomeText">What feels heavy today?</p>
                            <p class="welcome-note" id="welcomeNote">You can write naturally. Your message is not saved by this website.</p>
                        </article>
                    </div>

                    <form class="composer" id="chatForm">
                        <label class="sr-only" for="message">Your message</label>
                        <textarea id="message" name="message" rows="2" maxlength="2000" placeholder="I feel anxious about what comes next…" required></textarea>
                        <div class="composer-footer">
                            <span class="privacy-note">Private by default</span>
                            <button class="send-button" type="submit">
                                <span>Receive guidance</span>
                                <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <section class="daily-reading" aria-labelledby="dailyTitle">
                <p class="daily-date"><time datetime="<?= $manilaNow->format('Y-m-d') ?>"><?= $manilaNow->format('F j, Y') ?></time></p>
                <p class="eyebrow" id="dailyTitle">Verse for today</p>
                <blockquote id="dailyVerse"><?= htmlspecialchars($dailyVerses['en']['text'], ENT_QUOTES, 'UTF-8') ?></blockquote>
                <p class="daily-reference" id="dailyReference"><?= htmlspecialchars($dailyVerses['en']['reference'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="daily-translation" id="dailyTranslation"><?= htmlspecialchars($dailyVerses['en']['translation'], ENT_QUOTES, 'UTF-8') ?></p>
            </section>

            <p class="boundary-note">Still Waters offers Scripture-centered encouragement, not emergency, medical, or mental-health care. If you are in immediate danger, contact local emergency services or a trusted person now.</p>
        </main>

        <footer>
            <span>Made to help people pause, pray, and return to Scripture.</span>
        </footer>
    </div>

    <aside class="chat-widget" id="chatWidget" aria-label="Floating Scripture companion">
        <div class="chat-widget-panel" id="chatWidgetPanel" hidden></div>
        <button class="chat-widget-launcher" id="openChatWidget" type="button" aria-expanded="false" aria-controls="chatWidgetPanel">
            <span class="widget-bubble"><strong>Scripture companion</strong><span>Share what is on your heart</span></span>
            <span class="widget-mark" aria-hidden="true">
                <svg class="rosary-icon" viewBox="0 0 40 46" focusable="false">
                    <path d="M20 4C10.8 4 6 10.3 6 17.2c0 8.2 7.2 13.8 14 17.1 6.8-3.3 14-8.9 14-17.1C34 10.3 29.2 4 20 4Z"/>
                    <circle cx="20" cy="4" r="2.2"/><circle cx="11.5" cy="7.2" r="2.2"/><circle cx="6.8" cy="14.2" r="2.2"/><circle cx="7.8" cy="22" r="2.2"/><circle cx="13" cy="28.5" r="2.2"/>
                    <circle cx="28.5" cy="7.2" r="2.2"/><circle cx="33.2" cy="14.2" r="2.2"/><circle cx="32.2" cy="22" r="2.2"/><circle cx="27" cy="28.5" r="2.2"/>
                    <circle cx="20" cy="34.2" r="2.2"/><path d="M20 36.5V44M16.5 40h7"/>
                </svg>
            </span>
        </button>
    </aside>

    <dialog id="aboutDialog" class="about-dialog">
        <button class="dialog-close" id="closeAbout" type="button" aria-label="Close about dialog">×</button>
        <p class="eyebrow">About this companion</p>
        <h2>Scripture first, technology second.</h2>
        <p>Still Waters retrieves reviewed passages before composing a reflection. Scripture text is displayed from a verified source rather than generated from memory.</p>
        <p>This is a Christ-centered reflection tool, not Jesus himself and not a source of divine revelation. It is designed to help you pray, listen, and return to the words and witness of Scripture.</p>
        <h3>Translations</h3>
        <p>English passages use the public-domain World English Bible Updated in modern American English. Cebuano passages use Biblica® Open Ang Pulong sa Dios™ 2024 under CC BY-SA 4.0.</p>
        <p class="license-copy">Biblica® Open Ang Pulong sa Dios™ · Katungod sa pagmantala © 2009, 2010, 2014, 2024 Biblica, Inc. · Biblica® Open Cebuano Contemporary Bible™ · Copyright © 2009, 2010, 2014, 2024 by Biblica, Inc. · Used with permission. The original Work is available for free at biblica.com and open.bible.</p>
        <a class="text-link" href="https://ebible.org/cebocb/copyright.htm" target="_blank" rel="noopener noreferrer">Read the complete Cebuano license</a>
    </dialog>

    <script>window.STILL_WATERS = { csrf: "<?= $csrf ?>", dailyVerses: <?= $dailyJson ?> };</script>
    <script src="assets/app.js?v=6" defer></script>
</body>
</html>

