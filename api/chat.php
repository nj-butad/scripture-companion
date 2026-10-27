<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

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
$history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    jsonResponse(['error' => 'Your session expired. Refresh the page and try again.'], 419);
}

if ($message === '' || mb_strlen($message) > 2000) {
    jsonResponse(['error' => 'Please enter a message between 1 and 2,000 characters.'], 422);
}

$history = array_slice(array_values(array_filter(array_map(
    static function (mixed $turn): ?array {
        if (!is_array($turn)) {
            return null;
        }

        $user = trim((string) ($turn['user'] ?? ''));
        $assistant = trim((string) ($turn['assistant'] ?? ''));
        if ($user === '' || $assistant === '') {
            return null;
        }

        return [
            'user' => mb_substr($user, 0, 800),
            'assistant' => mb_substr($assistant, 0, 1200),
            'passage_ids' => array_slice(array_values(array_filter(
                array_map('strval', is_array($turn['passage_ids'] ?? null) ? $turn['passage_ids'] : []),
                static fn (string $id): bool => preg_match('/^[a-z0-9-]{1,80}$/', $id) === 1
            )), 0, 2),
        ];
    }, $history))), -3);

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
jsonResponse($chat->respond($message, $language, $history));

