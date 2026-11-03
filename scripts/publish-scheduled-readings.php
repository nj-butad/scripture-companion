<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Asia/Manila');
require dirname(__DIR__) . '/src/bootstrap.php';

use ScriptureCompanion\Database;

$db = Database::connection();
$db->exec("SET time_zone = '+08:00'");
$lock = $db->query("SELECT GET_LOCK('kristohanon_scheduled_readings', 0)")->fetchColumn();
if ((int) $lock !== 1) {
    fwrite(STDOUT, "Another scheduled-reading job is active.\n");
    exit(0);
}

$db->exec("CREATE TABLE IF NOT EXISTS feed_readings (
    id VARCHAR(120) NOT NULL,
    passage_id VARCHAR(120) NOT NULL,
    language ENUM('en','ceb') NOT NULL,
    topic VARCHAR(60) NOT NULL,
    title VARCHAR(180) NOT NULL,
    reflection TEXT NOT NULL,
    practice VARCHAR(500) NOT NULL,
    prayer TEXT NOT NULL,
    publish_on DATE NOT NULL,
    status ENUM('scheduled','published') NOT NULL DEFAULT 'scheduled',
    source_batch VARCHAR(40) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY feed_readings_schedule (language, status, publish_on),
    KEY feed_readings_passage (passage_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$passages = require dirname(__DIR__) . '/data/passages.php';
$passagesById = [];
foreach ($passages as $passage) {
    $passagesById[(string) $passage['id']] = $passage;
}
$scheduledReadings = require dirname(__DIR__) . '/data/monthly-readings.php';
$batchLanguages = [];
foreach ($scheduledReadings as $reading) {
    $batchKey = (string) ($reading['batch'] ?? '') . '|' . (string) ($reading['publish_on'] ?? '');
    $batchLanguages[$batchKey][(string) ($reading['language'] ?? '')] = true;
}
foreach ($batchLanguages as $batchKey => $languages) {
    if (!isset($languages['en'], $languages['ceb'])) {
        fwrite(STDERR, "Scheduled batch/date must include both English and Cebuano: {$batchKey}\n");
        exit(1);
    }
}

$upsert = $db->prepare(
    "INSERT INTO feed_readings (id, passage_id, language, topic, title, reflection, practice, prayer, publish_on, status, source_batch)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE passage_id = VALUES(passage_id), language = VALUES(language), topic = VALUES(topic),
       title = VALUES(title), reflection = VALUES(reflection), practice = VALUES(practice), prayer = VALUES(prayer),
       publish_on = VALUES(publish_on), status = VALUES(status), source_batch = VALUES(source_batch)"
);
$count = 0;
$today = date('Y-m-d');

foreach ($scheduledReadings as $reading) {
    $id = (string) ($reading['id'] ?? '');
    $passageId = (string) ($reading['passage_id'] ?? '');
    $language = (string) ($reading['language'] ?? '');
    $publishOn = (string) ($reading['publish_on'] ?? '');
    if ($id === '' || !isset($passagesById[$passageId]) || !in_array($language, ['en', 'ceb'], true)
        || ($passagesById[$passageId]['language'] ?? '') !== $language
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishOn)
        || !checkdate((int) substr($publishOn, 5, 2), (int) substr($publishOn, 8, 2), (int) substr($publishOn, 0, 4))) {
        fwrite(STDERR, "Invalid scheduled reading: {$id}\n");
        exit(1);
    }

    foreach (['topic', 'title', 'reflection', 'practice', 'prayer', 'batch'] as $required) {
        if (!isset($reading[$required]) || trim((string) $reading[$required]) === '') {
            fwrite(STDERR, "Scheduled reading {$id} is missing {$required}.\n");
            exit(1);
        }
    }

    $status = $publishOn <= $today ? 'published' : 'scheduled';
    $upsert->execute([
        $id,
        $passageId,
        $language,
        mb_substr((string) $reading['topic'], 0, 60),
        mb_substr((string) $reading['title'], 0, 180),
        (string) $reading['reflection'],
        mb_substr((string) $reading['practice'], 0, 500),
        (string) $reading['prayer'],
        $publishOn,
        $status,
        mb_substr((string) $reading['batch'], 0, 40),
    ]);
    $count++;
}

$publishDue = $db->prepare("UPDATE feed_readings SET status = 'published' WHERE status = 'scheduled' AND publish_on <= ?");
$publishDue->execute([$today]);
$published = $publishDue->rowCount();
$db->query("SELECT RELEASE_LOCK('kristohanon_scheduled_readings')");
fwrite(STDOUT, sprintf("Synced %d reviewed readings; published %d newly due readings.\n", $count, $published));
