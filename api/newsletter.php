<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use ScriptureCompanion\MailService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    jsonResponse(['error' => 'Invalid request.'], 400);
}

if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) {
    jsonResponse(['error' => 'This form expired. Refresh the page and try again.'], 419);
}

if (trim((string) ($payload['website'] ?? '')) !== '') {
    jsonResponse(['message' => 'Thank you for joining.']);
}

$email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) {
    jsonResponse(['error' => 'Enter a valid email address.'], 422);
}

$lastSignup = (int) ($_SESSION['newsletter_last_signup'] ?? 0);
if ($lastSignup > 0 && time() - $lastSignup < 10) {
    jsonResponse(['error' => 'Please wait a moment before trying again.'], 429);
}

$storageDir = dirname(__DIR__) . '/storage';
$storageFile = $storageDir . '/newsletter-subscribers.csv';
if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true) && !is_dir($storageDir)) {
    jsonResponse(['error' => 'We could not save your subscription. Please try again later.'], 500);
}

$existing = [];
if (is_file($storageFile)) {
    $readHandle = fopen($storageFile, 'rb');
    if ($readHandle !== false) {
        while (($row = fgetcsv($readHandle)) !== false) {
            if (isset($row[1])) {
                $existing[mb_strtolower(trim((string) $row[1]))] = true;
            }
        }
        fclose($readHandle);
    }
}

if (isset($existing[$email])) {
    $_SESSION['newsletter_last_signup'] = time();
    jsonResponse(['message' => 'You’re already on the Kristohanon newsletter list.']);
}

$writeHandle = fopen($storageFile, 'ab');
if ($writeHandle === false || !flock($writeHandle, LOCK_EX)) {
    if (is_resource($writeHandle)) {
        fclose($writeHandle);
    }
    jsonResponse(['error' => 'We could not save your subscription. Please try again later.'], 500);
}

$saved = fputcsv($writeHandle, [gmdate('c'), $email]);
flock($writeHandle, LOCK_UN);
fclose($writeHandle);
if ($saved === false) {
    jsonResponse(['error' => 'We could not save your subscription. Please try again later.'], 500);
}

$_SESSION['newsletter_last_signup'] = time();
MailService::sendToInbox(
    'New Kristohanon newsletter subscriber',
    "A visitor joined the Kristohanon newsletter.\n\nEmail: {$email}\nSigned up: " . date('F j, Y g:i A T')
);

jsonResponse(['message' => 'You’re on the list. Watch your inbox for quiet notes from Kristohanon.']);
