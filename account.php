<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';
header('Cache-Control: no-store, private');

use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use ScriptureCompanion\AuthService;
use ScriptureCompanion\Config;
use ScriptureCompanion\Database;
use ScriptureCompanion\MailService;

$mode = (string) ($_GET['mode'] ?? 'login');
$allowedModes = ['login', 'register', 'verify', 'forgot', 'reset', 'logout'];
if (!in_array($mode, $allowedModes, true)) {
    $mode = 'login';
}

if (!isset($_SESSION['account_csrf'])) {
    $_SESSION['account_csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$notice = '';
$sentVerification = false;
$authReady = AuthService::available();
$auth = $authReady ? AuthService::auth() : null;
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '/feed');
if (!preg_match('~^/(?!/)~', $next) || str_starts_with($next, '//')) {
    $next = '/feed';
}

if ($mode === 'logout' && $authReady) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['account_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $auth->logOut();
        session_regenerate_id(true);
        header('Location: /?signedout=1', true, 303);
        exit;
    }
    $mode = 'login';
}

if ($mode === 'verify' && $authReady) {
    try {
        $auth->confirmEmail((string) ($_GET['selector'] ?? ''), (string) ($_GET['token'] ?? ''));
        $notice = 'Your email is verified. You can now sign in and set up your Scripture feed.';
        $mode = 'login';
    } catch (TokenExpiredException) {
        $errors[] = 'That verification link has expired. Create a new account or contact us for help.';
    } catch (Throwable) {
        $errors[] = 'We could not verify that link. Check that you opened the complete link from your email.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['account_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'This form expired. Refresh the page and try again.';
    } elseif (!$authReady) {
        $errors[] = 'Accounts are being prepared. Please try again later.';
    } else {
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        try {
            if ($mode === 'register' && $errors === []) {
                if (!empty($_POST['website'])) {
                    $notice = 'Check your email for a verification link.';
                } elseif (($_POST['consent'] ?? '') !== 'yes') {
                    $errors[] = 'Please review and accept the privacy information to create an account.';
                } elseif (mb_strlen($password) < 12 || mb_strlen($password) > 4096) {
                    throw new InvalidPasswordException('Use a passphrase with at least 12 characters.');
                } else {
                    $baseUrl = rtrim(Config::get('APP_URL', 'https://kristohanon.com'), '/');
                    $mailSent = false;
                    $auth->register($email, $password, null, static function (string $selector, string $token) use ($email, $baseUrl, &$mailSent): void {
                        $url = $baseUrl . '/account/verify?selector=' . rawurlencode($selector) . '&token=' . rawurlencode($token);
                        $mailSent = MailService::send($email, 'Verify your Kristohanon account', "Peace be with you.\n\nConfirm your email address to open your private Scripture feed:\n{$url}\n\nIf you did not create an account, you can ignore this message.");
                    });
                    if (!$mailSent) {
                        $notice = 'Your account was created. We could not send the verification email yet; please contact info@kristohanon.com for help.';
                    } else {
                        $notice = 'Your account was created. Check your inbox for a verification link before signing in.';
                    }
                    $mode = 'login';
                }
            } elseif ($mode === 'login' && $errors === []) {
                $auth->login($email, $password, !empty($_POST['remember']) ? 2592000 : null);
                session_regenerate_id(true);
                header('Location: ' . $next, true, 303);
                exit;
            } elseif ($mode === 'forgot' && $errors === []) {
                $baseUrl = rtrim(Config::get('APP_URL', 'https://kristohanon.com'), '/');
                $auth->forgotPassword($email, static function (string $selector, string $token) use ($email, $baseUrl): void {
                    $url = $baseUrl . '/account/reset?selector=' . rawurlencode($selector) . '&token=' . rawurlencode($token);
                    MailService::send($email, 'Reset your Kristohanon password', "We received a request to reset your password.\n\nChoose a new password here:\n{$url}\n\nIf you did not request this, ignore this message.");
                });
                $notice = 'If an account exists for that email, a reset link will arrive shortly.';
            } elseif ($mode === 'reset' && $errors === []) {
                if (mb_strlen($password) < 12 || mb_strlen($password) > 4096) {
                    throw new InvalidPasswordException('Use a passphrase with at least 12 characters.');
                }
                $auth->resetPassword((string) ($_POST['selector'] ?? ''), (string) ($_POST['token'] ?? ''), $password);
                $notice = 'Your password has been changed. You can sign in now.';
                $mode = 'login';
            }
        } catch (InvalidEmailException) {
            $errors[] = 'Enter a valid email address.';
        } catch (InvalidPasswordException $error) {
            $errors[] = $mode === 'register' ? 'Use a passphrase with at least 12 characters.' : 'The email or password was not recognized.';
        } catch (UserAlreadyExistsException) {
            $errors[] = 'An account already exists for that email. Try signing in or resetting the password.';
        } catch (EmailNotVerifiedException) {
            $errors[] = 'Please verify your email from the link we sent before signing in.';
        } catch (TooManyRequestsException) {
            $errors[] = 'Too many attempts. Please wait a little while before trying again.';
        } catch (Throwable) {
            $errors[] = 'We could not complete that request. Please try again or contact info@kristohanon.com.';
        }
    }
}

$title = match ($mode) {
    'register' => 'Create your account',
    'forgot' => 'Reset your password',
    'reset' => 'Choose a new password',
    default => 'Welcome back',
};
$description = match ($mode) {
    'register' => 'Create a private Kristohanon account to save verses and receive a Scripture feed shaped by your preferences.',
    'forgot' => 'Request a secure password reset link for your Kristohanon account.',
    'reset' => 'Choose a new password for your Kristohanon account.',
    default => 'Sign in to your private Kristohanon Scripture and prayer account.',
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="description" content="<?= $escape($description) ?>">
    <meta name="robots" content="noindex, follow">
    <title><?= $escape($title) ?> — Kristohanon</title>
    <link rel="icon" href="/assets/images/kristohanon-app-icon-192.png" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/kristohanon-apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gelasio:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css?v=33">
</head>
<body>
    <a class="skip-link" href="#accountMain">Skip to account form</a>
    <div class="page-shell">
        <header class="site-header account-site-header">
            <a class="brand" href="/" aria-label="Kristohanon home"><img class="brand-mark" src="/assets/images/kristohanon-holy-face-painted.webp" alt="" width="32" height="32"><span>Kristohanon</span></a>
            <nav class="site-nav" aria-label="Main navigation"><a class="nav-link" href="/">Home</a><a class="nav-link" href="/rosary">Rosary</a><a class="nav-link" href="/prayers">Prayers</a><a class="nav-link" href="/about">About</a><a class="nav-link" href="/contact">Contact</a></nav>
            <a class="header-guidance" href="/#share">Receive guidance</a>
        </header>
        <main class="account-main" id="accountMain">
            <section class="account-card" aria-labelledby="accountTitle">
                <p class="eyebrow">A place to return to</p>
                <h1 id="accountTitle"><?= $escape($title) ?></h1>
                <p class="account-intro">Save Scripture that speaks to you and shape a personal feed for your walk with God.</p>

                <?php if ($notice !== ''): ?><p class="account-message is-notice" role="status"><?= $escape($notice) ?></p><?php endif; ?>
                <?php foreach ($errors as $error): ?><p class="account-message is-error" role="alert"><?= $escape($error) ?></p><?php endforeach; ?>

                <?php if (!$authReady): ?>
                    <div class="account-setup-note"><strong>Accounts are not available yet.</strong><p>The Hostinger database has not been connected. You can still use Scripture, prayers, and Rosary without signing in.</p><a href="/contact">Contact Kristohanon for help</a></div>
                <?php elseif ($mode === 'register'): ?>
                    <form class="account-form" method="post" action="/account/register">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['account_csrf']) ?>">
                        <input type="hidden" name="next" value="<?= $escape($next) ?>">
                        <label>Email address<input type="email" name="email" autocomplete="email" maxlength="249" required value="<?= $escape((string) ($_POST['email'] ?? '')) ?>"></label>
                        <label>Passphrase<input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="4096" required><small>Use at least 12 characters. A few words are easier to remember.</small></label>
                        <label class="account-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                        <label class="account-checkbox"><input type="checkbox" name="consent" value="yes" required><span>I agree to the <a href="/privacy">privacy information</a> and understand I can delete my account later.</span></label>
                        <button class="account-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
                    </form>
                    <p class="account-switch">Already have an account? <a href="/account/login?next=<?= rawurlencode($next) ?>">Sign in</a></p>
                <?php elseif ($mode === 'forgot'): ?>
                    <form class="account-form" method="post" action="/account/forgot">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['account_csrf']) ?>">
                        <label>Email address<input type="email" name="email" autocomplete="email" maxlength="249" required></label>
                        <button class="account-submit" type="submit">Send reset link <span aria-hidden="true">→</span></button>
                    </form>
                    <p class="account-switch"><a href="/account/login">Return to sign in</a></p>
                <?php elseif ($mode === 'reset'): ?>
                    <form class="account-form" method="post" action="/account/reset">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['account_csrf']) ?>">
                        <input type="hidden" name="selector" value="<?= $escape((string) ($_GET['selector'] ?? '')) ?>">
                        <input type="hidden" name="token" value="<?= $escape((string) ($_GET['token'] ?? '')) ?>">
                        <label>New passphrase<input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="4096" required></label>
                        <button class="account-submit" type="submit">Update password <span aria-hidden="true">→</span></button>
                    </form>
                <?php else: ?>
                    <form class="account-form" method="post" action="/account/login">
                        <input type="hidden" name="csrf" value="<?= $escape($_SESSION['account_csrf']) ?>">
                        <input type="hidden" name="next" value="<?= $escape($next) ?>">
                        <label>Email address<input type="email" name="email" autocomplete="email" maxlength="249" required value="<?= $escape((string) ($_POST['email'] ?? '')) ?>"></label>
                        <label>Password<input type="password" name="password" autocomplete="current-password" maxlength="4096" required></label>
                        <label class="account-checkbox"><input type="checkbox" name="remember" value="yes"><span>Keep me signed in on this device</span></label>
                        <button class="account-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
                    </form>
                    <div class="account-links"><a href="/account/forgot">Forgot your password?</a><a href="/account/register">Create an account</a></div>
                <?php endif; ?>
                <p class="account-privacy">Your Scripture and prayer activity is private. We never use your private chat messages to personalize the feed unless you choose to save a topic.</p>
            </section>
        </main>
        <footer><span>Made to help people pause, pray, and return to Scripture.</span><nav class="footer-links" aria-label="Trust and transparency"><a href="/privacy">Privacy</a><a href="/safety">Safety</a><a href="/contact">Contact</a></nav></footer>
    </div>
</body>
</html>
