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
        && $englishPassages[$index]['id'] !== 'webu-psa-55-23-23'
));
$manilaNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
$today = $manilaNow->setTime(0, 0);
$weekStart = $today->modify('monday this week');
$weekVerses = [];
$cebuanoDays = ['Lunes', 'Martes', 'Miyerkoles', 'Huwebes', 'Biyernes', 'Sabado', 'Domingo'];
$cebuanoDaysShort = ['Lun', 'Mar', 'Miy', 'Huw', 'Biy', 'Sab', 'Dom'];
for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
    $date = $weekStart->modify("+{$dayOffset} days");
    $verseIndex = $dailyCandidates[(int) $date->format('z') % count($dailyCandidates)];
    $weekVerses[] = [
        'date' => $date->format('Y-m-d'),
        'day' => $date->format('D'),
        'day_long' => $date->format('l'),
        'day_ceb' => $cebuanoDaysShort[$dayOffset],
        'day_long_ceb' => $cebuanoDays[$dayOffset],
        'date_label' => $date->format('F j, Y'),
        'is_today' => $date->format('Y-m-d') === $today->format('Y-m-d'),
        'verses' => ['en' => $englishPassages[$verseIndex], 'ceb' => $cebuanoPassages[$verseIndex]],
    ];
}
$todayWeekIndex = (int) $today->format('N') - 1;
$dailyVerses = $weekVerses[$todayWeekIndex]['verses'];
$weekJson = json_encode($weekVerses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
$home = require __DIR__ . '/data/home.php';
$homeJson = json_encode($home, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="A gentle, bilingual Scripture companion for difficult moments.">
    <title>Kristohanon — Scripture Companion</title>
    <link rel="icon" href="assets/images/kristohanon-mark.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="image" href="assets/images/hero-still-waters.webp" fetchpriority="high">
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=23">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Kristohanon home">
                <img class="brand-mark" src="assets/images/kristohanon-mark.svg" alt="" width="32" height="32">
                <span>Kristohanon</span>
            </a>
            <nav class="site-nav" aria-label="Main navigation">
                <a class="nav-link is-active" href="/" aria-current="page">Home</a>
                <a class="nav-link" href="rosary.php">Rosary</a>
                <a class="nav-link" href="prayers.php">Prayers</a>
                <a class="nav-link" href="about.php">About</a>
            </nav>
            <a class="header-guidance" href="#share">Receive guidance</a>
        </header>

        <main id="mainContent">
            <section class="intro" aria-labelledby="pageTitle">
                <div class="intro-copy">
                    <p class="eyebrow">A quiet place to bring what you carry</p>
                    <h1 id="pageTitle">You don’t have to find the right words.</h1>
                    <p class="lede">Share what is on your heart. We’ll meet you with Scripture, a short reflection, and a simple prayer.</p>
                </div>
            </section>

            <nav class="journey-nav" aria-label="Explore today’s page">
                <a href="#share"><span>01</span> Share</a>
                <a href="#verseToday"><span>02</span> Scripture</a>
                <a href="#prayToday"><span>03</span> Pray</a>
                <a href="#discoverToday"><span>04</span> Discover</a>
            </nav>

            <div class="companion-home" id="share">
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
                        <div class="composer-invitation" id="composerInvitation"><span aria-hidden="true">✦</span><strong>Start a private Scripture conversation</strong></div>
                        <label class="sr-only" for="message">Your message</label>
                        <textarea id="message" name="message" rows="1" maxlength="2000" placeholder="I feel anxious about what comes next…" required></textarea>
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

            <section class="daily-reading" id="verseToday" aria-labelledby="dailyTitle">
                <div class="week-picker" id="weekPicker" role="tablist" aria-label="Verses for this week">
                    <?php foreach ($weekVerses as $index => $day): ?>
                        <button type="button" role="tab" class="week-day<?= $day['is_today'] ? ' is-active' : '' ?>" data-verse-day="<?= $index ?>" aria-selected="<?= $day['is_today'] ? 'true' : 'false' ?>">
                            <span><?= htmlspecialchars($day['day'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong><?= $weekStart->modify("+{$index} days")->format('j') ?></strong>
                            <?php if ($day['is_today']): ?><em>Today</em><?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="daily-slide" id="dailySlide" role="tabpanel" aria-live="polite">
                    <p class="daily-date"><time id="dailyDate" datetime="<?= $today->format('Y-m-d') ?>"><?= $today->format('F j, Y') ?></time></p>
                    <p class="eyebrow" id="dailyTitle">Verse for today</p>
                    <blockquote id="dailyVerse"><?= htmlspecialchars($dailyVerses['en']['text'], ENT_QUOTES, 'UTF-8') ?></blockquote>
                    <p class="daily-reference" id="dailyReference"><?= htmlspecialchars($dailyVerses['en']['reference'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="daily-translation" id="dailyTranslation"><?= htmlspecialchars($dailyVerses['en']['translation'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="daily-slider-actions">
                    <button type="button" id="previousVerse" aria-label="Previous day">←</button>
                    <span id="versePosition"><?= $todayWeekIndex + 1 ?> of 7</span>
                    <button type="button" id="nextVerse" aria-label="Next day">→</button>
                </div>
            </section>

            <section class="home-section" id="prayToday" aria-labelledby="prayTodayTitle">
                <div class="section-heading">
                    <p class="eyebrow">A small rhythm for today</p>
                    <h2 id="prayTodayTitle">Pause, listen, and respond</h2>
                </div>
                <div class="practice-grid">
                    <article class="practice-card lectio-card" id="lectioCard">
                        <div class="card-heading-row"><div><p class="card-kicker">3-minute prayer</p><h3 id="lectioTitle">Pray with today’s verse</h3></div><span class="time-chip">3 min</span></div>
                        <img class="lectio-art" src="assets/images/lectio-bible.webp" alt="An open Bible beside a candle and olive branch" loading="lazy" width="900" height="600">
                        <div class="lectio-progress" aria-hidden="true"><span class="is-active"></span><span></span><span></span></div>
                        <div class="lectio-content" id="lectioContent" aria-live="polite">
                            <p class="lectio-step" id="lectioStep">1 · Read</p>
                            <p class="lectio-instruction" id="lectioInstruction">Read the passage slowly. Notice the word or phrase that stays with you.</p>
                        </div>
                        <div class="card-actions"><button class="text-button" id="resetLectio" type="button" hidden>Start again</button><button class="compact-primary" id="nextLectio" type="button">Begin <span aria-hidden="true">→</span></button></div>
                    </article>

                    <article class="practice-card moment-card" id="momentCard">
                        <p class="card-kicker" id="momentLabel"><?= htmlspecialchars($home['moment']['en']['label'], ENT_QUOTES, 'UTF-8') ?></p>
                        <h3 id="momentTitle"><?= htmlspecialchars($home['moment']['en']['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="moment-prayer" id="momentText"><?= htmlspecialchars($home['moment']['en']['text'], ENT_QUOTES, 'UTF-8') ?></p>
                        <button class="mark-prayed" id="markMomentPrayed" type="button"><span aria-hidden="true">○</span> Mark as prayed</button>
                    </article>

                    <article class="practice-card gratitude-card" id="gratitudeCard">
                        <p class="card-kicker">1-minute practice</p>
                        <h3 id="gratitudeTitle">Name one grace</h3>
                        <p class="gratitude-copy" id="gratitudeCopy">What small gift, kindness, or moment of help can you thank God for today?</p>
                        <label class="sr-only" for="gratitudeText">A grace I noticed today</label>
                        <textarea id="gratitudeText" rows="3" maxlength="400" placeholder="Today, I’m grateful for…"></textarea>
                        <div class="card-actions"><span class="private-microcopy" id="gratitudePrivacy">Stays on this screen</span><button class="compact-primary" id="offerGratitude" type="button">Offer thanks</button></div>
                    </article>
                </div>
            </section>

            <section class="home-section" aria-labelledby="needTitle">
                <div class="section-heading compact-heading"><p class="eyebrow">Come as you are</p><h2 id="needTitle">What do you need today?</h2><p>Choose what feels closest. Your Scripture companion will help you begin.</p></div>
                <div class="need-grid" id="needGrid">
                    <?php foreach ($home['needs'] as $need): ?>
                        <button class="need-card" type="button" data-need="<?= htmlspecialchars($need['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <span aria-hidden="true"><?= htmlspecialchars($need['symbol'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong><?= htmlspecialchars($need['en']['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-section" id="discoverToday" aria-labelledby="discoverTitle">
                <div class="section-heading editorial-heading">
                    <p class="eyebrow">Learn and remember</p>
                    <h2 id="discoverTitle">A little more for the journey</h2>
                </div>
                <div class="discovery-grid">
                <article class="discovery-card saint-card" id="saintCard">
                    <img class="saint-art" src="assets/images/church-calendar.webp" alt="Sunlight through a stained-glass church window" loading="lazy" width="720" height="1080">
                    <div class="saint-content">
                    <p class="card-kicker">Today in the Church</p>
                    <h2 id="saintName">Today belongs to God</h2>
                    <p class="saint-type" id="saintType">Daily Catholic calendar</p>
                    <p id="saintDescription">The Church’s calendar helps us remember that every ordinary day belongs to God.</p>
                    <p class="saint-prompt" id="saintPrompt"><strong>Carry this today:</strong> Practice quiet faithfulness in the task before you.</p>
                    <a class="saint-source" href="https://github.com/cpbjr/catholic-readings-api" target="_blank" rel="noopener noreferrer">Calendar data source</a>
                    </div>
                </article>

                <article class="discovery-card quiz-card" id="quizCard">
                    <p class="card-kicker">Daily Catholic question</p>
                    <h2 id="quizQuestion"><?= htmlspecialchars($home['quiz']['question']['en'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="quiz-options" id="quizOptions">
                        <?php foreach ($home['quiz']['options'] as $optionIndex => $option): ?>
                            <button type="button" data-quiz-answer="<?= $optionIndex ?>"><?= htmlspecialchars($option['en'], ENT_QUOTES, 'UTF-8') ?></button>
                        <?php endforeach; ?>
                    </div>
                    <p class="quiz-feedback" id="quizFeedback" aria-live="polite" hidden></p>
                </article>
                <article class="discovery-card term-card" id="termCard">
                    <p class="card-kicker" id="termKicker">Catholic word for today</p>
                    <h2 id="termName"><?= htmlspecialchars($home['term']['en']['term'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p id="termDefinition"><?= htmlspecialchars($home['term']['en']['definition'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="term-practice" id="termPractice"><strong>Try this:</strong> <?= htmlspecialchars($home['term']['en']['practice'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="text-link term-link" href="prayers.php">Continue learning through prayer <span aria-hidden="true">→</span></a>
                </article>
                </div>
            </section>

            <section class="home-section rhythm-card" aria-labelledby="rhythmTitle">
                <div><p class="card-kicker">Private on this device</p><h2 id="rhythmTitle">Your quiet rhythm</h2><p id="rhythmMessage">A gentle record of showing up—never a score.</p></div>
                <div class="rhythm-week" id="rhythmWeek" aria-label="Prayer activity this week"></div>
                <div class="rhythm-links"><a href="rosary.php">Continue the Rosary <span aria-hidden="true">→</span></a><a href="prayers.php" id="recentPrayerLink">Open the prayer library <span aria-hidden="true">→</span></a></div>
            </section>

            <section class="seasonal-card <?= htmlspecialchars($home['seasonal']['tone'], ENT_QUOTES, 'UTF-8') ?>" aria-labelledby="seasonalTitle">
                <p class="eyebrow" id="seasonalEyebrow"><?= htmlspecialchars($home['seasonal']['en']['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
                <h2 id="seasonalTitle"><?= htmlspecialchars($home['seasonal']['en']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p id="seasonalText"><?= htmlspecialchars($home['seasonal']['en']['text'], ENT_QUOTES, 'UTF-8') ?></p>
                <a class="seasonal-action" id="seasonalAction" href="<?= htmlspecialchars($home['seasonal']['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($home['seasonal']['en']['action'], ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">→</span></a>
            </section>

            <p class="boundary-note">Kristohanon offers Scripture-centered encouragement, not emergency, medical, or mental-health care. If you are in immediate danger, contact local emergency services or a trusted person now.</p>
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
                <img class="kristohanon-widget-mark" src="assets/images/kristohanon-mark-inverse.svg" alt="" width="38" height="38">
            </span>
        </button>
    </aside>

    <script>window.STILL_WATERS = { csrf: "<?= $csrf ?>", weekVerses: <?= $weekJson ?>, todayIndex: <?= $todayWeekIndex ?>, home: <?= $homeJson ?> };</script>
    <script src="assets/app.js?v=13" defer></script>
</body>
</html>

