<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    jsonResponse(['error' => 'JSON is required.'], 415);
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($payload) || !hash_equals($_SESSION['csrf'] ?? '', (string) ($payload['csrf'] ?? ''))) {
    jsonResponse(['error' => 'Your session expired. Refresh the page and try again.'], 419);
}

$now = time();
$recent = array_values(array_filter(
    $_SESSION['feedback_requests'] ?? [],
    static fn (int $timestamp): bool => $timestamp > $now - 3600
));
if (count($recent) >= 10) {
    jsonResponse(['error' => 'Thank you. Please wait before sending more feedback.'], 429);
}

$kind = (string) ($payload['kind'] ?? '');
$allowedKinds = ['helpful', 'not_helpful', 'report'];
$allowedReasons = ['incorrect_scripture', 'incorrect_prayer', 'unsafe', 'irrelevant', 'translation', 'other'];
if (!in_array($kind, $allowedKinds, true)) {
    jsonResponse(['error' => 'Choose a valid feedback option.'], 422);
}

$reason = (string) ($payload['reason'] ?? '');
if ($kind === 'report' && !in_array($reason, $allowedReasons, true)) {
    jsonResponse(['error' => 'Choose what needs attention.'], 422);
}

$comment = trim((string) ($payload['comment'] ?? ''));
$assistant = trim((string) ($payload['assistant_response'] ?? ''));
$includeMessage = ($payload['include_message'] ?? false) === true;
$userMessage = $includeMessage ? trim((string) ($payload['user_message'] ?? '')) : '';
if (mb_strlen($comment) > 500 || mb_strlen($assistant) > 6000 || mb_strlen($userMessage) > 2000) {
    jsonResponse(['error' => 'The feedback is too long. Please shorten it and try again.'], 422);
}

$record = [
    'id' => bin2hex(random_bytes(8)),
    'created_at' => gmdate('c'),
    'kind' => $kind,
    'reason' => $kind === 'report' ? $reason : null,
    'comment' => $comment !== '' ? $comment : null,
    'language' => ($payload['language'] ?? 'en') === 'ceb' ? 'ceb' : 'en',
    'response_type' => mb_substr((string) ($payload['response_type'] ?? ''), 0, 30),
    'assistant_response' => $kind === 'report' ? $assistant : null,
    'user_message' => $kind === 'report' && $includeMessage ? $userMessage : null,
];

$storage = dirname(__DIR__) . '/storage';
if (!is_dir($storage) && !mkdir($storage, 0750, true) && !is_dir($storage)) {
    jsonResponse(['error' => 'Feedback could not be saved right now. Please try again later.'], 503);
}

$encoded = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($encoded) || file_put_contents($storage . '/feedback.ndjson', $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
    jsonResponse(['error' => 'Feedback could not be saved right now. Please try again later.'], 503);
}

$recent[] = $now;
$_SESSION['feedback_requests'] = $recent;
jsonResponse(['ok' => true, 'message' => 'Thank you. Your feedback was received.']);
