<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use ScriptureCompanion\PastoralChat;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (!str_starts_with($contentType, 'application/json')) {
    jsonResponse(['error' => 'JSON is required.'], 415);
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$message = trim((string) ($payload['message'] ?? ''));
$language = ($payload['language'] ?? 'en') === 'ceb' ? 'ceb' : 'en';
$csrf = (string) ($payload['csrf'] ?? '');

if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    jsonResponse(['error' => 'Your session expired. Refresh the page and try again.'], 419);
}

if ($message === '' || mb_strlen($message) > 2000) {
    jsonResponse(['error' => 'Please enter a message between 1 and 2,000 characters.'], 422);
}

$now = time();
$recent = array_values(array_filter(
    $_SESSION['requests'] ?? [],
    static fn (int $timestamp): bool => $timestamp > $now - 60
));
if (count($recent) >= 8) {
    jsonResponse(['error' => 'Please wait a moment before sending another message.'], 429);
}
$recent[] = $now;
$_SESSION['requests'] = $recent;

$chat = new PastoralChat();
jsonResponse($chat->respond($message, $language));

