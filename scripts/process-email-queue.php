<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Asia/Manila');

require dirname(__DIR__) . '/src/bootstrap.php';

use ScriptureCompanion\Database;
use ScriptureCompanion\MailService;

$db = Database::connection();
$db->exec("SET time_zone = '+08:00'");
$lock = $db->query("SELECT GET_LOCK('kristohanon_reading_email_queue', 0)")->fetchColumn();
if ((int) $lock !== 1) {
    fwrite(STDOUT, "Another email queue worker is active.\n");
    exit(0);
}

$sent = 0;
$failed = 0;
try {
    $messages = $db->query(
        "SELECT id, recipient, subject, body, attempts FROM email_queue WHERE status = 'pending' AND available_at <= CURRENT_TIMESTAMP ORDER BY created_at, id LIMIT 10"
    )->fetchAll();
    $update = $db->prepare(
        "UPDATE email_queue SET status = ?, attempts = attempts + 1, sent_at = ?, available_at = CASE WHEN ? = 'pending' THEN DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE) ELSE available_at END WHERE id = ? AND status = 'pending'"
    );

    foreach ($messages as $message) {
        $delivered = MailService::send((string) $message['recipient'], (string) $message['subject'], (string) $message['body']);
        $attempts = (int) $message['attempts'] + 1;
        $status = $delivered ? 'sent' : ($attempts >= 3 ? 'failed' : 'pending');
        $sentAt = $delivered ? date('Y-m-d H:i:s') : null;
        $update->execute([$status, $sentAt, $status, (int) $message['id']]);
        if ($delivered) {
            $sent++;
        } else {
            $failed++;
        }
    }
} finally {
    $db->query("SELECT RELEASE_LOCK('kristohanon_reading_email_queue')");
}

fwrite(STDOUT, sprintf("Sent %d reading emails; %d failed this attempt.\n", $sent, $failed));
