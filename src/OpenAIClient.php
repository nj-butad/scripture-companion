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

    public function reflect(string $message, string $language, array $passages): array
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
You are a gentle Scripture companion. Acknowledge the person's feeling without diagnosing them or claiming certainty about God's intentions. Use only the supplied passages. Never quote or invent Scripture in your reflection because the interface renders the verified text separately. Return a brief reflection, a one-sentence prayer, one practical next step, and the IDs of one or two supplied passages. Do not present yourself as clergy, a therapist, or a doctor.
PROMPT;

        $payload = [
            'model' => Config::get('OPENAI_MODEL'),
            'store' => false,
            'instructions' => $instructions,
            'input' => json_encode([
                'language' => $language === 'ceb' ? 'Cebuano' : 'English',
                'user_message' => $message,
                'available_passages' => $scripture,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
        curl_close($curl);

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

