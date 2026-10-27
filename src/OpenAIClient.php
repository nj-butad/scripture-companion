<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use RuntimeException;

final class OpenAIClient
{
    public function isConfigured(): bool
    {
        return Config::get('OPENAI_API_KEY') !== '' && Config::get('OPENAI_MODEL') !== '';
    }

    public function reflect(string $message, string $language, array $passages, array $history = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('OpenAI is not configured.');
        }

        $scripture = array_map(static fn (array $passage): array => [
            'id' => $passage['id'],
            'reference' => $passage['reference'],
            'translation' => $passage['translation'],
            'text' => $passage['text'],
        ], $passages);

        $instructions = <<<'PROMPT'
You are a gentle, attentive, Christ-centered Scripture companion having a real conversation. You are not Jesus, God, a prophet, or a divine authority, and must never speak as though you are. Respond to the specific details and emotional tone of the newest message, using recent conversation only for continuity. Never start with a generic stock phrase such as "I am sorry this feels heavy" or "It is understandable." Vary your opening, sentence structure, prayer, and next step. If the person repeats or follows up on a concern, deepen the response instead of restating the previous answer. Do not diagnose, preach at the person, or claim certainty about God's intentions. Carefully choose the one or two supplied passages that most directly fit this particular message; prefer a relevant Gospel passage showing the words or actions of Jesus when one is genuinely appropriate, but do not force it. Do not reuse a passage listed in recent conversation when another strong choice exists. Use only the supplied passages. Never quote or invent Scripture in the reflection because the interface renders verified text separately. Write in the requested language. Return a concise reflection of 2–4 sentences, a specific one-sentence prayer, one practical next step, and the IDs of one or two supplied passages. Do not present yourself as clergy, a therapist, or a doctor.
PROMPT;

        $payload = [
            'model' => Config::get('OPENAI_MODEL'),
            'store' => false,
            'instructions' => $instructions,
            'input' => json_encode([
                'language' => $language === 'ceb' ? 'Cebuano' : 'English',
                'user_message' => $message,
                'recent_conversation' => $history,
                'available_passages' => $scripture,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'max_output_tokens' => 500,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'pastoral_response',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'reflection' => ['type' => 'string'],
                            'prayer' => ['type' => 'string'],
                            'next_step' => ['type' => 'string'],
                            'passage_ids' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'minItems' => 1,
                                'maxItems' => 2,
                            ],
                        ],
                        'required' => ['reflection', 'prayer', 'next_step', 'passage_ids'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        $curl = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . Config::get('OPENAI_API_KEY'),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);

        if (!is_string($raw) || $status < 200 || $status >= 300) {
            throw new RuntimeException('OpenAI request failed: ' . ($error ?: 'HTTP ' . $status));
        }

        $response = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $outputText = '';
        foreach ($response['output'] ?? [] as $item) {
            if (($item['type'] ?? '') !== 'message') {
                continue;
            }
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? '') === 'output_text') {
                    $outputText .= (string) ($content['text'] ?? '');
                }
            }
        }

        return json_decode($outputText, true, 512, JSON_THROW_ON_ERROR);
    }

    public function guide(string $message, string $language, array $knowledge, array $history = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('OpenAI is not configured.');
        }

        $instructions = <<<'PROMPT'
You are the knowledgeable, gentle guide inside Kristohanon, a bilingual Catholic Scripture and prayer companion for Filipino life. Answer the newest question directly and naturally using only the supplied project knowledge. You may explain how to pray the Rosary, reproduce an exact supplied prayer, describe site features, explain privacy and translation sources, or recount the supplied creator testimony. Do not add facts, prayers, doctrine, biography, endorsements, or promises that are absent from the supplied knowledge. When exact prayer text is supplied and requested, preserve it accurately. Never promise that faith will produce money, jobs, healing, or miracles; distinguish personal testimony from a universal guarantee. You are not Jesus, God, clergy, a therapist, or divine revelation. Do not impersonate a sacred person or claim certainty about God's private will. Write in the requested language. Use short paragraphs and be complete without becoming repetitive. The next step should point to the most relevant supplied URL when one exists.
PROMPT;

        $payload = [
            'model' => Config::get('OPENAI_MODEL'),
            'store' => false,
            'instructions' => $instructions,
            'input' => json_encode([
                'language' => $language === 'ceb' ? 'Cebuano' : 'English',
                'user_message' => $message,
                'recent_conversation' => $history,
                'project_knowledge' => $knowledge,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'max_output_tokens' => 800,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'project_guide_response',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'answer' => ['type' => 'string'],
                            'next_step' => ['type' => 'string'],
                        ],
                        'required' => ['answer', 'next_step'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        return $this->request($payload);
    }

    private function request(array $payload): array
    {
        $curl = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . Config::get('OPENAI_API_KEY'),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);

        if (!is_string($raw) || $status < 200 || $status >= 300) {
            throw new RuntimeException('OpenAI request failed: ' . ($error ?: 'HTTP ' . $status));
        }

        $response = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $outputText = '';
        foreach ($response['output'] ?? [] as $item) {
            if (($item['type'] ?? '') !== 'message') {
                continue;
            }
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? '') === 'output_text') {
                    $outputText .= (string) ($content['text'] ?? '');
                }
            }
        }

        return json_decode($outputText, true, 512, JSON_THROW_ON_ERROR);
    }
}

