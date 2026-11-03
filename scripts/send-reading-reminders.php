<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Asia/Manila');

require dirname(__DIR__) . '/src/bootstrap.php';

use ScriptureCompanion\Database;
use ScriptureCompanion\PersonalizationService;

$db = Database::connection();
$db->exec("SET time_zone = '+08:00'");
$lock = $db->query("SELECT GET_LOCK('kristohanon_reading_reminders', 0)")->fetchColumn();
if ((int) $lock !== 1) {
    fwrite(STDOUT, "Another reminder job is active.\n");
    exit(0);
}

$today = date('Y-m-d');
$isWeeklySendDay = date('N') === '1';
$users = $db->query(
    "SELECT u.id, u.email, COALESCE(p.email_digest, 'off') AS email_digest FROM users u LEFT JOIN user_preferences p ON p.user_id = u.id WHERE u.verified = 1 ORDER BY u.id"
)->fetchAll();
$queued = 0;
$notified = 0;

foreach ($users as $user) {
    $userId = (int) $user['id'];
    $frequency = (string) $user['email_digest'];
    $passage = PersonalizationService::feed($userId, 1)[0] ?? null;
    if (!is_array($passage)) {
        continue;
    }

    $title = 'Your Scripture reading for ' . date('F j');
    $message = (string) $passage['reference'] . ' is ready in your personalized Scripture feed.';
    $existingNotification = $db->prepare(
        "SELECT id FROM notifications WHERE user_id = ? AND notification_type = 'reading_reminder' AND DATE(created_at) = ? LIMIT 1"
    );
    $existingNotification->execute([$userId, $today]);
    if ($existingNotification->fetchColumn() === false) {
        $insertNotification = $db->prepare(
            "INSERT INTO notifications (user_id, notification_type, title, message, action_url) VALUES (?, 'reading_reminder', ?, ?, '/feed')"
        );
        $insertNotification->execute([$userId, $title, $message]);
        $notified++;
    }

    if ($frequency === 'off' || ($frequency === 'weekly' && !$isWeeklySendDay)) {
        continue;
    }

    $period = $frequency === 'weekly' ? date('o-\WW') : $today;
    $subject = 'Your Kristohanon reading · ' . $period;
    $existingEmail = $db->prepare('SELECT id FROM email_queue WHERE user_id = ? AND subject = ? LIMIT 1');
    $existingEmail->execute([$userId, $subject]);
    if ($existingEmail->fetchColumn() !== false) {
        continue;
    }

    $url = rtrim(\ScriptureCompanion\Config::get('APP_URL', 'https://kristohanon.com'), '/') . '/feed';
    $preferencesUrl = rtrim(\ScriptureCompanion\Config::get('APP_URL', 'https://kristohanon.com'), '/') . '/settings';
    $body = "Peace be with you,\n\nHere is a Scripture reading selected for your language and interests.\n\n"
        . (string) $passage['reference'] . "\n\n“" . (string) $passage['text'] . "”\n\n"
        . (string) $passage['translation'] . "\n\nReturn to your feed: {$url}\n\n"
        . "Change or turn off these reminders in your account preferences: {$preferencesUrl}";
    $insertEmail = $db->prepare(
        "INSERT INTO email_queue (user_id, recipient, subject, body) VALUES (?, ?, ?, ?)"
    );
    $insertEmail->execute([$userId, (string) $user['email'], $subject, $body]);
    $queued++;
}

fwrite(STDOUT, sprintf("Queued %d reading emails and created %d in-app notifications.\n", $queued, $notified));
$db->query("SELECT RELEASE_LOCK('kristohanon_reading_reminders')");
