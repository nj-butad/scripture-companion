<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(24));
}

$values = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'subject' => trim((string) ($_POST['subject'] ?? 'General inquiry')),
    'message' => trim((string) ($_POST['message'] ?? '')),
];
$subjects = ['General inquiry', 'Prayer and Scripture', 'Catholic faith question', 'Partnership', 'Technical issue'];
$errors = [];
$previewed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['contact_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'This form expired. Refresh the page and try again.';
    }
    if (mb_strlen($values['name']) < 2 || mb_strlen($values['name']) > 80) {
        $errors[] = 'Enter your name using 2 to 80 characters.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 160) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!in_array($values['subject'], $subjects, true)) {
        $errors[] = 'Choose a valid subject.';
    }
    if (mb_strlen($values['message']) < 20 || mb_strlen($values['message']) > 3000) {
        $errors[] = 'Write a message between 20 and 3,000 characters.';
    }

    if ($errors === []) {
        $previewed = true;
        $values = ['name' => '', 'email' => '', 'subject' => 'General inquiry', 'message' => ''];
        $_SESSION['contact_csrf'] = bin2hex(random_bytes(24));
    }
}

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="Contact Kristohanon about Scripture, Catholic faith questions, partnerships, or technical concerns.">
    <title>Contact Kristohanon</title>
    <link rel="icon" href="/assets/images/kristohanon-app-icon-192.png" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/kristohanon-apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Kristohanon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=27">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Kristohanon home"><img class="brand-mark" src="/assets/images/kristohanon-holy-face-painted.webp" alt="" width="32" height="32"><span>Kristohanon</span></a>
            <nav class="site-nav" aria-label="Main navigation"><a class="nav-link" href="/">Home</a><a class="nav-link" href="/rosary">Rosary</a><a class="nav-link" href="/prayers">Prayers</a><a class="nav-link" href="/about">About</a><a class="nav-link is-active" href="/contact" aria-current="page">Contact</a></nav>
            <a class="header-guidance" href="/#share">Receive guidance</a>
        </header>

        <main class="contact-main" id="mainContent">
            <header class="contact-intro">
                <h1>Let’s stay connected</h1>
                <p>Ask about Kristohanon, share a correction, suggest a prayer resource, or begin a conversation about supporting this ministry.</p>
            </header>

            <div class="contact-layout">
                <aside class="contact-details" aria-labelledby="contactDetailsTitle">
                    <h2 id="contactDetailsTitle">Contact details</h2>
                    <p class="contact-temporary">Temporary placeholders</p>
                    <dl>
                        <div><dt>Email</dt><dd>hello@kristohanon.example</dd></div>
                        <div><dt>Phone</dt><dd>+63 900 000 0000</dd></div>
                        <div><dt>Location</dt><dd>Southern Leyte, Philippines</dd></div>
                    </dl>

                    <h3>Follow Kristohanon</h3>
                    <div class="social-links" aria-label="Social media links coming soon">
                        <span class="social-link is-disabled" aria-label="Facebook, coming soon" title="Facebook · Coming soon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v2H6v4h3v6h4v-6h3l1-4h-4V9c0-.7.3-1 1-1Z"/></svg>
                        </span>
                        <span class="social-link is-disabled" aria-label="Instagram, coming soon" title="Instagram · Coming soon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".8" class="social-dot"/></svg>
                        </span>
                        <span class="social-link is-disabled" aria-label="YouTube, coming soon" title="YouTube · Coming soon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7.2c-.2-1-1-1.8-2-2C16.4 5 12 5 12 5s-4.4 0-6 .2c-1 .2-1.8 1-2 2C3.8 8.5 3.8 12 3.8 12s0 3.5.2 4.8c.2 1 1 1.8 2 2 1.6.2 6 .2 6 .2s4.4 0 6-.2c1-.2 1.8-1 2-2 .2-1.3.2-4.8.2-4.8s0-3.5-.2-4.8Z"/><path d="m10 9 5 3-5 3V9Z" class="social-play"/></svg>
                        </span>
                    </div>
                    <p class="contact-coming-soon">Official accounts will be linked here when they are ready.</p>
                </aside>

                <section class="contact-form-section" aria-labelledby="contactFormTitle">
                    <h2 id="contactFormTitle">Send a message</h2>
                    <p class="form-disclosure">This preview form is prepared for a future Hostinger Reach connection. For now, submissions are validated but are not sent, saved, or added to a mailing list.</p>

                    <?php if ($previewed): ?>
                        <div class="contact-status is-ready" role="status"><strong>The form is ready.</strong><span>Your message passed validation, but it was not sent or saved because email delivery is not connected yet.</span></div>
                    <?php elseif ($errors !== []): ?>
                        <div class="contact-status is-error" role="alert"><strong>Please check the form.</strong><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>

                    <form class="contact-form" method="post" action="/contact">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['contact_csrf']) ?>">
                        <div class="contact-field-row">
                            <label><span>Name</span><input type="text" name="name" value="<?= $escape($values['name']) ?>" autocomplete="name" minlength="2" maxlength="80" required></label>
                            <label><span>Email</span><input type="email" name="email" value="<?= $escape($values['email']) ?>" autocomplete="email" maxlength="160" required></label>
                        </div>
                        <label><span>What is this about?</span><select name="subject" required><?php foreach ($subjects as $subject): ?><option<?= $values['subject'] === $subject ? ' selected' : '' ?>><?= $escape($subject) ?></option><?php endforeach; ?></select></label>
                        <label><span>Message</span><textarea name="message" rows="7" minlength="20" maxlength="3000" placeholder="Write your message here…" required><?= $escape($values['message']) ?></textarea><small>20–3,000 characters</small></label>
                        <button class="contact-submit" type="submit">Check message <span aria-hidden="true">→</span></button>
                    </form>
                </section>
            </div>
        </main>

        <footer><span>Made to help people pause, pray, and return to Scripture.</span><nav class="footer-links" aria-label="Trust and transparency"><a href="/privacy">Privacy</a><a href="/safety">Safety</a><a href="/sources">Sources</a><a href="/contact">Contact</a></nav></footer>
    </div>
</body>
</html>
