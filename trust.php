<?php

declare(strict_types=1);

$page = (string) ($_GET['page'] ?? 'privacy');
$pages = [
    'privacy' => [
        'title' => 'Privacy, in plain language',
        'lede' => 'Kristohanon is designed to let you begin without an account and without building a private profile about your prayer life.',
        'sections' => [
            ['What stays on your device', 'Rosary progress, gratitude text, recent prayer activity, quiz state, and similar preferences use your browser’s local storage. They are not synced to a Kristohanon account because accounts do not exist. Clearing this site’s browser data removes them.'],
            ['What happens when you chat', 'Your message is sent to Kristohanon’s server and then to the configured OpenAI service so a response can be composed. Kristohanon does not write ordinary chat messages to its own feedback log or database. OpenAI requests are configured with storage disabled.'],
            ['Feedback and reports', 'Helpful and Not helpful ratings contain no original message or generated answer. A report includes the generated answer so it can be reviewed. Your original message is included only when you deliberately check the consent box before submitting the report. Reports are stored in a private server file and are not public.'],
            ['What we do not collect', 'Kristohanon does not require your name, email address, account, contacts, precise location, camera, or microphone. The feedback system does not intentionally record your IP address or browser identity. Ordinary hosting security logs may still be maintained by the hosting provider.'],
            ['Your choices', 'Do not enter information you do not want transmitted to the AI provider. You can use the Rosary, prayer library, weekly Scripture, and other non-chat features without submitting a personal message.'],
        ],
    ],
    'safety' => [
        'title' => 'A companion with clear boundaries',
        'lede' => 'Kristohanon offers Scripture-centered encouragement. It is not Jesus, divine revelation, clergy, therapy, medical care, or emergency support.',
        'sections' => [
            ['How guidance is prepared', 'The app first retrieves reviewed local Scripture candidates. AI then composes a short reflection using those candidates, while verified Scripture text is rendered separately with its reference and translation.'],
            ['What the companion must never claim', 'It must not impersonate Jesus, God, a saint, a prophet, priest, doctor, or therapist. It must not diagnose, promise healing or wealth, or claim certainty about God’s private will for a person.'],
            ['When immediate help matters', 'If you may harm yourself or someone else, are in immediate danger, or face a medical emergency, contact local emergency services now and reach a trusted person who can stay with you. Do not rely on this website for urgent care.'],
            ['Spiritual and pastoral support', 'For confession, sacramental questions, serious spiritual distress, or guidance requiring personal knowledge of your situation, speak with a trusted priest, pastor, or qualified professional.'],
            ['Help improve safety', 'Every generated answer includes a report option. Please report unsafe advice, invented Scripture, inappropriate certainty, or content that could harm someone.'],
        ],
    ],
    'sources' => [
        'title' => 'Where the words come from',
        'lede' => 'Scripture, devotional material, generated reflection, and calendar information have different sources. Kristohanon keeps those roles distinct.',
        'sections' => [
            ['English Scripture', 'English passages use the World English Bible Updated, a contemporary American-English translation in the public domain. Scripture quotations are selected from Kristohanon’s reviewed local library.'],
            ['Cebuano Scripture', 'Cebuano passages use Biblica® Open Ang Pulong sa Dios™ 2024 under CC BY-SA 4.0 with attribution. The original work is available from Biblica and open.bible.'],
            ['Catholic prayers and the Rosary', 'Traditional English prayer structure follows public guidance from the Holy See and USCCB. Verified Cebuano Rosary and Angelus wording follows the Basilica Minore del Sto. Niño de Cebu; other Cebuano texts are identified as devotional translations.'],
            ['Calendar information', 'Daily Catholic calendar context is enhanced with public data from the cpbjr Catholic Readings API. A local fallback remains visible when the remote calendar cannot be reached.'],
            ['AI-generated reflection', 'OpenAI composes the conversational reflection from locally retrieved material. Generated reflection is not Scripture, Church teaching, divine revelation, or a substitute for pastoral care.'],
        ],
    ],
];

if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'privacy';
}
$content = $pages[$page];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="Kristohanon <?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?> information in plain language.">
    <title><?= htmlspecialchars($content['title'], ENT_QUOTES, 'UTF-8') ?> — Kristohanon</title>
    <link rel="icon" href="/assets/images/kristohanon-holy-face.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/kristohanon-apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Kristohanon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=25">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Kristohanon home"><img class="brand-mark" src="/assets/images/kristohanon-holy-face.svg" alt="" width="32" height="32"><span>Kristohanon</span></a>
            <nav class="site-nav" aria-label="Main navigation"><a class="nav-link" href="/">Home</a><a class="nav-link" href="/rosary">Rosary</a><a class="nav-link" href="/prayers">Prayers</a><a class="nav-link" href="/about">About</a></nav>
            <a class="header-guidance" href="/#share">Receive guidance</a>
        </header>
        <main class="trust-main" id="mainContent">
            <header class="trust-header"><h1><?= htmlspecialchars($content['title'], ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars($content['lede'], ENT_QUOTES, 'UTF-8') ?></p></header>
            <nav class="trust-nav" aria-label="Trust and transparency"><a<?= $page === 'privacy' ? ' aria-current="page"' : '' ?> href="/privacy">Privacy</a><a<?= $page === 'safety' ? ' aria-current="page"' : '' ?> href="/safety">Safety</a><a<?= $page === 'sources' ? ' aria-current="page"' : '' ?> href="/sources">Sources</a></nav>
            <article class="trust-article">
                <?php foreach ($content['sections'] as [$heading, $body]): ?>
                    <section><h2><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></p></section>
                <?php endforeach; ?>
            </article>
        </main>
        <footer><span>Made to help people pause, pray, and return to Scripture.</span><nav class="footer-links" aria-label="Trust and transparency"><a href="/privacy">Privacy</a><a href="/safety">Safety</a><a href="/sources">Sources</a></nav></footer>
    </div>
</body>
</html>
