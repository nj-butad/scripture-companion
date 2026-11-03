<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

use ScriptureCompanion\MailService;

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
$sent = false;

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

    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        $sent = true;
    }

    if ($errors === [] && !$sent) {
        $body = "New message from Kristohanon.com\n\n"
            . "Name: {$values['name']}\n"
            . "Email: {$values['email']}\n"
            . "Subject: {$values['subject']}\n\n"
            . "Message:\n{$values['message']}\n";

        if (MailService::sendToInbox('Kristohanon contact: ' . $values['subject'], $body, $values['email'])) {
            $sent = true;
            $values = ['name' => '', 'email' => '', 'subject' => 'General inquiry', 'message' => ''];
            $_SESSION['contact_csrf'] = bin2hex(random_bytes(24));
        } else {
            $errors[] = 'Your message could not be delivered. Please email info@kristohanon.com directly.';
        }
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
    <link rel="stylesheet" href="assets/style.css?v=38">
</head>
<body>
    <a class="skip-link" href="#mainContent">Skip to main content</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="/" aria-label="Kristohanon home"><img class="brand-mark" src="/assets/images/kristohanon-holy-face-painted.webp" alt="" width="32" height="32"><span class="brand-copy"><span class="brand-name">Kristohanon</span><span class="brand-tagline">Kauban sa imong adlaw-adlaw nga pagtuo.</span></span></a>
            <nav class="site-nav" aria-label="Main navigation"><a class="nav-link" href="/">Home</a><a class="nav-link" href="/rosary">Rosary</a><a class="nav-link" href="/prayers">Prayers</a><a class="nav-link" href="/about">About</a><a class="nav-link is-active" href="/contact" aria-current="page">Contact</a></nav>
            <div class="header-actions"><?php require __DIR__ . '/partials/account-menu.php'; ?><a class="header-guidance" href="/#share">Receive guidance</a></div>
        </header>

        <main class="contact-main" id="mainContent">
            <div class="contact-layout">
                <section class="contact-opening" aria-labelledby="contactTitle">
                <header class="contact-intro">
                    <h1 id="contactTitle">A note, from one heart to another.</h1>
                    <p>I started Kristohanon after Scripture became a steady place to return to in uncertain days. If something here has helped you—or you have a question, correction, or prayer resource to share—I would be glad to hear from you.</p>
                    <p class="contact-location">Southern Leyte, Philippines</p>
                </header>
                <figure class="contact-image">
                    <img src="/assets/images/lectio-bible.webp" alt="An open Bible beside a candle and olive branch">
                    <figcaption>A little room for a conversation, and a word to carry with you.</figcaption>
                </figure>
                </section>

                <div class="contact-sidebar">
                <section class="contact-direct" aria-labelledby="contactDirectTitle">
                    <h2 id="contactDirectTitle">Reach me directly</h2>
                    <nav class="contact-routes" aria-label="Direct contact options">
                        <a href="tel:+639166014705"><span>Call</span><strong>+63 916 601 4705</strong><svg class="contact-route-arrow" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 14 14 6M6 6h8v8"/></svg></a>
                        <a href="mailto:info@kristohanon.com"><span>Email</span><strong>info@kristohanon.com</strong><svg class="contact-route-arrow" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 14 14 6M6 6h8v8"/></svg></a>
                        <a href="https://www.facebook.com/profile.php?id=61596336594040" target="_blank" rel="noopener noreferrer"><span>Facebook</span><strong>Kristohanon</strong><svg class="contact-route-arrow" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 14 14 6M6 6h8v8"/></svg></a>
                    </nav>
                </section>

                <section class="contact-form-section" aria-labelledby="contactFormTitle">
                    <h2 id="contactFormTitle">Write me a message</h2>
                    <p class="contact-form-lede">Questions about Scripture, Catholic prayers, or how this project began are welcome. Your note goes to <a href="mailto:info@kristohanon.com">info@kristohanon.com</a> and is not added to the newsletter.</p>

                    <?php if ($sent): ?>
                        <div class="contact-status is-ready" role="status"><strong>Your message is on its way.</strong><span>Thank you. Kristohanon will reply to the email address you provided.</span></div>
                    <?php elseif ($errors !== []): ?>
                        <div class="contact-status is-error" role="alert"><strong>Please check the form.</strong><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>

                    <form class="contact-form" method="post" action="/contact">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['contact_csrf']) ?>">
                        <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        <div class="contact-field-row">
                            <label><span>Name</span><input type="text" name="name" value="<?= $escape($values['name']) ?>" autocomplete="name" minlength="2" maxlength="80" required></label>
                            <label><span>Email</span><input type="email" name="email" value="<?= $escape($values['email']) ?>" autocomplete="email" maxlength="160" required></label>
                        </div>
                        <label><span>What is this about?</span><select name="subject" required><?php foreach ($subjects as $subject): ?><option<?= $values['subject'] === $subject ? ' selected' : '' ?>><?= $escape($subject) ?></option><?php endforeach; ?></select></label>
                        <label><span>Message</span><textarea name="message" rows="7" minlength="20" maxlength="3000" placeholder="Write your message here…" required><?= $escape($values['message']) ?></textarea><small>20–3,000 characters</small></label>
                        <button class="contact-submit" type="submit">Send message <span aria-hidden="true">→</span></button>
                    </form>
                </section>
                </div>
            </div>
        </main>

        <footer><span>Made to help people pause, pray, and return to Scripture.</span><nav class="footer-links" aria-label="Trust and transparency"><a href="mailto:info@kristohanon.com">info@kristohanon.com</a><a href="/privacy">Privacy</a><a href="/safety">Safety</a><a href="/sources">Sources</a><a href="/contact">Contact</a></nav></footer>
    </div>
    <?php require __DIR__ . '/partials/chat-widget.php'; ?>
</body>
</html>
