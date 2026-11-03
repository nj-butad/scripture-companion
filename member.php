<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';
header('Cache-Control: no-store, private');

use ScriptureCompanion\AuthService;
use ScriptureCompanion\Database;
use ScriptureCompanion\PersonalizationService;

$page = (string) ($_GET['page'] ?? 'feed');
$afterId = (string) ($_GET['after'] ?? $_POST['after'] ?? '');
if (!in_array($page, ['feed', 'saved', 'settings', 'notifications'], true)) {
    http_response_code(404);
    exit('Not found');
}

$userId = AuthService::userId();
if ($userId === null) {
    header('Location: /account/login?next=' . rawurlencode('/' . $page), true, 303);
    exit;
}

$auth = AuthService::auth();
$csrf = $_SESSION['account_csrf'] ??= bin2hex(random_bytes(32));
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$notice = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'This form expired. Refresh the page and try again.';
    } else {
        try {
            if ($page === 'feed' && ($_POST['action'] ?? '') === 'save') {
                $passageId = (string) ($_POST['passage_id'] ?? '');
                $passages = require __DIR__ . '/data/passages.php';
                $validIds = array_column($passages, 'id');
                if (!in_array($passageId, $validIds, true)) {
                    throw new InvalidArgumentException('That passage is unavailable.');
                }
                $statement = Database::connection()->prepare('INSERT IGNORE INTO saved_passages (user_id, passage_id) VALUES (?, ?)');
                $statement->execute([$userId, $passageId]);
                PersonalizationService::record($userId, 'save', $passageId, (string) ($_POST['topic'] ?? ''), 3);
                $notice = 'Passage saved to your collection.';
            } elseif ($page === 'saved' && ($_POST['action'] ?? '') === 'remove') {
                $statement = Database::connection()->prepare('DELETE FROM saved_passages WHERE user_id = ? AND passage_id = ?');
                $statement->execute([$userId, (string) ($_POST['passage_id'] ?? '')]);
                $notice = 'Passage removed from your collection.';
            } elseif ($page === 'settings' && ($_POST['action'] ?? '') === 'preferences') {
                PersonalizationService::savePreferences(
                    $userId,
                    (string) ($_POST['language'] ?? 'en'),
                    is_array($_POST['topics'] ?? null) ? $_POST['topics'] : [],
                    (string) ($_POST['email_digest'] ?? 'weekly')
                );
                $notice = 'Your reading preferences have been saved.';
            } elseif ($page === 'notifications' && ($_POST['action'] ?? '') === 'read') {
                $statement = Database::connection()->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND read_at IS NULL');
                $statement->execute([$userId]);
                $notice = 'Notifications marked as read.';
            }
        } catch (Throwable) {
            $errors[] = 'We could not save that change. Please try again.';
        }
    }
}

$preferences = PersonalizationService::preferences($userId);
$title = match ($page) {
    'saved' => 'Saved Scripture',
    'settings' => 'Reading preferences',
    'notifications' => 'Notifications',
    default => 'Your Scripture feed',
};
$passages = [];
$notifications = [];
if ($page === 'feed') {
    $passages = PersonalizationService::feed($userId, 12, $afterId);
    $hasMore = count($passages) > 12;
    $passages = array_slice($passages, 0, 12);
    foreach ($passages as $passage) {
        PersonalizationService::record($userId, 'view', (string) $passage['id'], (string) ($passage['topic'] ?? ''), 0);
    }
} elseif ($page === 'saved') {
    $allPassages = require __DIR__ . '/data/passages.php';
    $byId = [];
    foreach ($allPassages as $passage) {
        $byId[$passage['id']] = $passage;
    }
    $statement = Database::connection()->prepare('SELECT passage_id FROM saved_passages WHERE user_id = ? ORDER BY saved_at DESC');
    $statement->execute([$userId]);
    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $id) {
        if (isset($byId[$id])) {
            $passages[] = $byId[$id];
        }
    }
} elseif ($page === 'notifications') {
    $statement = Database::connection()->prepare('SELECT notification_type, title, message, action_url, read_at, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $statement->execute([$userId]);
    $notifications = $statement->fetchAll();
}

$topics = PersonalizationService::availableTopics();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="robots" content="noindex, follow">
    <title><?= $escape($title) ?> — Kristohanon</title>
    <link rel="icon" href="/assets/images/kristohanon-app-icon-192.png" type="image/png">
    <link rel="stylesheet" href="/assets/style.css?v=37">
    <style>
        .member-main{width:min(100% - 36px,1080px);margin:0 auto;padding:48px 0 72px}.member-heading{display:flex;justify-content:space-between;align-items:end;gap:24px;margin-bottom:28px}.member-heading h1{margin:0;font-size:clamp(2rem,5vw,3.25rem)}.member-heading p{margin:8px 0 0;color:var(--ink-soft)}.member-nav{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 26px;padding:0 0 16px;border-bottom:1px solid var(--line)}.member-nav a{padding:9px 12px;border-radius:6px;color:var(--ink-soft);text-decoration:none}.member-nav a[aria-current=page]{color:var(--burgundy-dark);background:rgba(166,65,62,.1);font-weight:600}.member-actions{display:flex;align-items:center;gap:14px}.member-actions form{margin:0}.member-actions button,.member-form button,.save-form button{border:0;border-radius:6px;padding:10px 14px;background:var(--burgundy);color:white;font:inherit;font-weight:600;cursor:pointer}.member-message{margin:0 0 16px;padding:12px 14px;border:1px solid var(--line);border-radius:7px;background:var(--paper)}.member-muted{margin:8px 0 0;color:var(--ink-soft);font-size:.86rem;line-height:1.5}.member-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,310px),1fr));gap:18px}.member-card{overflow:hidden;border:1px solid var(--line);border-radius:12px;background:var(--paper)}.member-card figure{position:relative;margin:0;min-height:220px;padding:24px;background-size:cover;background-position:center;display:flex;flex-direction:column;justify-content:flex-end;gap:10px}.member-card figure blockquote{align-self:stretch;margin:0;padding:14px;border-radius:7px;background:rgba(255,253,250,.94);font-family:Gelasio,Georgia,serif;font-size:1.1rem;line-height:1.55}.daily-reading-label{align-self:flex-start;padding:5px 8px;color:var(--burgundy-deep);background:var(--paper);font-size:.7rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase}.reading-cover{align-self:stretch;padding:16px;border-radius:8px;background:rgba(255,253,250,.95)}.reading-cover .reading-date{margin:0 0 8px;color:var(--burgundy);font-size:.7rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase}.reading-cover h2{margin:0;color:var(--ink);font-family:Gelasio,Georgia,serif;font-size:1.35rem;line-height:1.2}.reading-cover p:last-child{margin:8px 0 0;color:var(--ink-soft);font-size:.82rem}.member-card-body{padding:18px}.member-card-body h2,.member-card-body h3{margin:0;color:var(--burgundy-dark);font-size:1rem}.member-card-body h3{margin-top:16px}.member-card-body p{color:var(--ink-soft);font-size:.88rem;line-height:1.55}.member-card-body .reading-reflection{font-size:.94rem}.member-card-body .reading-practice,.member-card-body .reading-prayer{padding-left:12px;border-left:2px solid var(--line)}.member-card-body form{margin-top:14px}.feed-cadence{margin:0 0 20px;color:var(--ink-soft);font-size:.92rem}.feed-more,.feed-end{margin:28px 0 0;text-align:center}.feed-end{color:var(--ink-soft);font-size:.9rem}.feed-more .secondary-button{display:inline-flex;min-height:44px;align-items:center}.member-form{max-width:650px;padding:24px;border:1px solid var(--line);border-radius:12px;background:var(--paper)}.member-form fieldset{margin:0 0 20px;padding:0;border:0}.member-form legend{margin-bottom:10px;font-weight:600}.member-form label{display:flex;gap:9px;align-items:center;margin:8px 0}.member-form select{min-height:44px;padding:8px;border:1px solid var(--line);border-radius:6px;background:var(--paper);font:inherit}.notification-list{display:grid;gap:12px}.notification-item{padding:18px;border:1px solid var(--line);border-radius:9px;background:var(--paper)}.notification-item h2{margin:0 0 6px;font-size:1rem}.notification-item p{margin:0;color:var(--ink-soft)}.notification-item time{font-size:.78rem;color:var(--ink-soft)}.member-empty{padding:28px;border:1px dashed var(--line);border-radius:10px;color:var(--ink-soft)}@media(max-width:640px){.member-main{width:min(100% - 24px,1080px);padding-top:30px}.member-heading{align-items:start;flex-direction:column}.member-actions{width:100%;justify-content:space-between}}
    </style>
</head>
<body>
<div class="page-shell">
    <header class="site-header account-site-header">
        <a class="brand" href="/" aria-label="Kristohanon home"><img class="brand-mark" src="/assets/images/kristohanon-holy-face-painted.webp" alt="" width="32" height="32"><span class="brand-copy"><span class="brand-name">Kristohanon</span><span class="brand-tagline">Kauban sa imong adlaw-adlaw nga pagtuo.</span></span></a>
        <nav class="site-nav" aria-label="Main navigation"><a class="nav-link" href="/">Home</a><a class="nav-link" href="/rosary">Rosary</a><a class="nav-link" href="/prayers">Prayers</a><a class="nav-link" href="/about">About</a><a class="nav-link" href="/contact">Contact</a></nav>
        <div class="header-actions"><?php require __DIR__ . '/partials/account-menu.php'; ?><a class="header-guidance" href="/#share">Receive guidance</a></div>
    </header>
    <main class="member-main">
        <div class="member-heading"><div><p class="eyebrow">Your private space</p><h1><?= $escape($title) ?></h1><p>Scripture for your walk, shaped by the topics and language you choose.</p></div></div>
        <nav class="member-nav" aria-label="Your account"><a href="/feed" <?= $page === 'feed' ? 'aria-current="page"' : '' ?>>For you</a><a href="/saved" <?= $page === 'saved' ? 'aria-current="page"' : '' ?>>Saved Scripture</a><a href="/notifications" <?= $page === 'notifications' ? 'aria-current="page"' : '' ?>>Notifications</a><a href="/settings" <?= $page === 'settings' ? 'aria-current="page"' : '' ?>>Preferences</a></nav>
        <?php if ($notice !== ''): ?><p class="member-message" role="status"><?= $escape($notice) ?></p><?php endif; ?>
        <?php foreach ($errors as $error): ?><p class="member-message" role="alert"><?= $escape($error) ?></p><?php endforeach; ?>
        <?php if ($page === 'settings'): ?>
            <form class="member-form" method="post" action="/settings"><input type="hidden" name="csrf" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="preferences">
                <fieldset><legend>Reading language</legend><label><input type="radio" name="language" value="en" <?= $preferences['language'] === 'en' ? 'checked' : '' ?>> English</label><label><input type="radio" name="language" value="ceb" <?= $preferences['language'] === 'ceb' ? 'checked' : '' ?>> Cebuano</label></fieldset>
                <fieldset><legend>Topics for your Scripture feed</legend><?php foreach ($topics as $topic): ?><label><input type="checkbox" name="topics[]" value="<?= $escape($topic['id']) ?>" <?= in_array($topic['id'], $preferences['topics'], true) ? 'checked' : '' ?>> <?= $escape($topic['label']) ?></label><?php endforeach; ?></fieldset>
                <label for="emailDigest">Email reading reminders</label><select id="emailDigest" name="email_digest"><?php foreach (['off' => 'Off', 'daily' => 'Daily', 'weekly' => 'Weekly'] as $value => $label): ?><option value="<?= $value ?>" <?= $preferences['email_digest'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><p class="member-muted">Reminders are off until you choose a schedule. You can change this at any time.</p>
                <p><button type="submit">Save preferences</button></p>
            </form>
        <?php elseif ($page === 'notifications'): ?>
            <?php if ($notifications !== []): ?><form method="post" action="/notifications"><input type="hidden" name="csrf" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="read"><button class="member-actions" type="submit">Mark all as read</button></form><div class="notification-list"><?php foreach ($notifications as $notification): ?><article class="notification-item"><h2><?= $escape((string) $notification['title']) ?></h2><p><?= $escape((string) $notification['message']) ?></p><time datetime="<?= $escape((string) $notification['created_at']) ?>"><?= $escape((string) $notification['created_at']) ?></time></article><?php endforeach; ?></div><?php else: ?><p class="member-empty">You’re all caught up. New account updates will appear here.</p><?php endif; ?>
        <?php else: ?>
            <?php if ($page === 'feed'): ?><p class="feed-cadence">A daily Scripture reading, shaped by your interests, with new reviewed reflections added to the feed each month.</p><?php endif; ?>
            <?php if ($passages !== []): ?><div class="member-cards"><?php foreach ($passages as $passage): ?><article class="member-card"><figure style="background-image:linear-gradient(0deg,rgba(34,25,22,.22),rgba(34,25,22,.02)),url('<?= $escape((string) ($passage['image'] ?? '/assets/images/hero-still-waters.webp')) ?>')"><?php if (isset($passage['devotional'])): ?><div class="reading-cover"><p class="reading-date">New reflection · <?= $escape((new DateTimeImmutable((string) $passage['devotional']['publish_on']))->format('F j, Y')) ?></p><h2><?= $escape((string) $passage['devotional']['title']) ?></h2><p><?= $escape((string) $passage['reference']) ?></p></div><?php else: ?><?php if (!empty($passage['is_daily'])): ?><span class="daily-reading-label">Today’s reading</span><?php endif; ?><blockquote>“<?= $escape((string) $passage['text']) ?>”</blockquote><?php endif; ?></figure><div class="member-card-body"><?php if (isset($passage['devotional'])): ?><h3><?= $escape((string) $passage['reference']) ?></h3><p><?= $escape((string) $passage['translation']) ?></p><p class="reading-reflection"><?= $escape((string) $passage['devotional']['reflection']) ?></p><p class="reading-practice"><strong>Try this:</strong> <?= $escape((string) $passage['devotional']['practice']) ?></p><p class="reading-prayer"><strong>A short prayer:</strong> <?= $escape((string) $passage['devotional']['prayer']) ?></p><?php else: ?><h2><?= $escape((string) $passage['reference']) ?></h2><p><?= $escape((string) $passage['translation']) ?></p><?php endif; ?><?php if ($page === 'feed'): ?><form class="save-form" method="post" action="/feed"><input type="hidden" name="csrf" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="after" value="<?= $escape($afterId) ?>"><input type="hidden" name="passage_id" value="<?= $escape((string) ($passage['passage_id'] ?? $passage['id'])) ?>"><input type="hidden" name="topic" value="<?= $escape((string) ($passage['topic'] ?? '')) ?>"><button type="submit">Save this passage</button></form><?php else: ?><form class="save-form" method="post" action="/saved"><input type="hidden" name="csrf" value="<?= $escape($csrf) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="passage_id" value="<?= $escape((string) $passage['id']) ?>"><button type="submit">Remove from saved</button></form><?php endif; ?></div></article><?php endforeach; ?></div>
                <?php if ($page === 'feed' && $hasMore): ?><p class="feed-more"><a class="secondary-button" href="/feed?after=<?= rawurlencode((string) end($passages)['id']) ?>">Show more Scripture</a></p><?php elseif ($page === 'feed'): ?><p class="feed-end">You’re caught up for now. New reviewed reflections are added to your feed each month, and the featured Scripture changes daily.</p><?php endif; ?>
            <?php else: ?><p class="member-empty"><?= $page === 'saved' ? 'You haven’t saved a passage yet. Explore your Scripture feed and keep the words you want to return to.' : 'No more readings in this section. Choose another topic or return tomorrow for the daily reading.' ?></p><?php endif; ?>
        <?php endif; ?>
    </main>
</div>
    <?php require __DIR__ . '/partials/chat-widget.php'; ?>
</body>
</html>
