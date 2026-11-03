<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use Throwable;

final class PastoralChat
{
    public function __construct(
        private readonly ScriptureRepository $scripture = new ScriptureRepository(),
        private readonly KnowledgeRepository $knowledge = new KnowledgeRepository(),
        private readonly SafetyService $safety = new SafetyService(),
        private readonly OpenAIClient $openAI = new OpenAIClient(),
    ) {
    }

    public function respond(string $message, string $language, array $history = []): array
    {
        if ($this->safety->needsUrgentResponse($message)) {
            return [
                'type' => 'urgent',
                ...$this->safety->urgentResponse($language),
                'passages' => [],
            ];
        }

        if ($this->isSocialOpening($message)) {
            return $this->socialResponse($message, $language, $history);
        }

        if ($this->knowledge->isKnowledgeQuery($message)) {
            return $this->answerKnowledge($message, $language, $history);
        }

        $excludedIds = [];
        foreach ($history as $turn) {
            foreach ($turn['passage_ids'] ?? [] as $id) {
                $excludedIds[] = (string) $id;
            }
        }

        $searchMessage = $this->scriptureSearchMessage($message, $history);
        $passages = $this->scripture->find($searchMessage, $language, array_values(array_unique($excludedIds)));
        $allowedIds = array_column($passages, 'id');

        try {
            $response = $this->openAI->reflect($message, $language, $passages, $history);
            $selectedIds = array_values(array_intersect($allowedIds, $response['passage_ids'] ?? []));
            $selected = array_values(array_filter(
                $passages,
                static fn (array $passage): bool => in_array($passage['id'], $selectedIds, true)
            ));

            if (($response['response_type'] ?? 'reflection') === 'conversation') {
                return [
                    'type' => 'conversation',
                    'message' => (string) $response['reflection'],
                    'prayer' => '',
                    'next_step' => (string) ($response['next_step'] ?? ''),
                    'passages' => [],
                    'ai' => true,
                ];
            }

            return [
                'type' => 'reflection',
                'message' => (string) $response['reflection'],
                'prayer' => (string) $response['prayer'],
                'next_step' => (string) $response['next_step'],
                'passages' => $selected ?: array_slice($passages, 0, 1),
                'ai' => true,
            ];
        } catch (Throwable) {
            return $this->fallback($language, $passages);
        }
    }

    private function isSocialOpening(string $message): bool
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', preg_replace('/[.!?,;:…]+/u', ' ', $message) ?? $message)));
        $english = [
            'hi', 'hello', 'hey', 'hiya', 'hi there', 'hello there', 'hey there',
            'good morning', 'good afternoon', 'good evening', 'how are you', 'how are you doing',
            "what's up", 'whats up', 'thanks', 'thank you', 'okay thanks', 'ok thanks',
            'got it', 'okay', 'ok', 'sure',
        ];
        $cebuano = [
            'kumusta', 'kamusta', 'maayong buntag', 'maayong hapon', 'maayong gabii',
            'salamat', 'sige', 'maayo ra', 'okay ra', 'oo',
        ];

        return in_array($normalized, [...$english, ...$cebuano], true);
    }

    private function socialResponse(string $message, string $language, array $history): array
    {
        $normalized = mb_strtolower(trim($message));
        $isThanks = preg_match('/^(thanks|thank you|salamat)([.!?,\s]*)$/iu', $normalized) === 1;

        if ($language === 'ceb') {
            $reply = $isThanks
                ? 'Walay sapayan. Gusto ka nga magpadayon ta sa pag-istorya, o naa pa kay laing gustong ipaambit?'
                : (count($history) > 0
                    ? 'Maayo nga nibalik ka. Ania ra ko maminaw—unsa may naa sa imong hunahuna o kasingkasing karon?'
                    : 'Kumusta. Maayo nga ania ka. Unsay naa sa imong hunahuna o kasingkasing karon?');
        } else {
            $reply = $isThanks
                ? 'You’re welcome. Would you like to keep talking, or is there something else on your mind?'
                : (count($history) > 0
                    ? 'I’m glad you came back. I’m here to listen—what’s on your mind today?'
                    : 'Hi. I’m glad you’re here. What’s on your mind today?');
        }

        return [
            'type' => 'conversation',
            'message' => $reply,
            'prayer' => '',
            'next_step' => '',
            'passages' => [],
            'ai' => false,
        ];
    }

    private function scriptureSearchMessage(string $message, array $history): string
    {
        // For a short follow-up like “that fear” or “tell me more,” include the
        // last thing the person shared so retrieval can stay with their context.
        $wordCount = preg_match_all('/[\p{L}\p{N}]+/u', $message);
        if ($wordCount > 7 || $history === []) {
            return $message;
        }

        $lastUserMessage = trim((string) ($history[array_key_last($history)]['user'] ?? ''));
        if ($lastUserMessage === '' || $this->isSocialOpening($lastUserMessage)) {
            return $message;
        }

        return $lastUserMessage . ' ' . $message;
    }

    private function answerKnowledge(string $message, string $language, array $history): array
    {
        $knowledge = $this->knowledge->find($message, $language);

        try {
            $response = $this->openAI->guide($message, $language, $knowledge, $history);

            return [
                'type' => 'guide',
                'message' => (string) $response['answer'],
                'prayer' => '',
                'next_step' => (string) $response['next_step'],
                'passages' => [],
                'ai' => true,
            ];
        } catch (Throwable) {
            $first = $knowledge[0] ?? null;
            $second = $knowledge[1] ?? null;
            $content = trim((string) ($first['content'] ?? ''));
            if ($second !== null && mb_strlen($content) < 700) {
                $content .= "\n\n" . trim((string) $second['content']);
            }

            return [
                'type' => 'guide',
                'message' => $content !== '' ? $content : ($language === 'ceb'
                    ? 'Pangutan-a ko bahin sa Rosaryo, Katolikong mga pag-ampo, Kristohanon, o sa mga feature niini nga site.'
                    : 'Ask me about the Rosary, Catholic prayers, Kristohanon, or the features of this site.'),
                'prayer' => '',
                'next_step' => $first !== null
                    ? ($language === 'ceb'
                        ? 'Padayon sa ' . $first['title'] . ': ' . ($first['url'] ?? '/')
                        : 'Continue with ' . $first['title'] . ': ' . ($first['url'] ?? '/'))
                    : ($language === 'ceb' ? 'Ablihi ang Home aron makita ang mga feature.' : 'Open Home to explore the available features.'),
                'passages' => [],
                'ai' => false,
            ];
        }
    }

    private function fallback(string $language, array $passages): array
    {
        if ($language === 'ceb') {
            return [
                'type' => 'reflection',
                'message' => 'Pasayloa nga bug-at ang imong gibati karon. Dili kinahanglan nga masulbad dayon ang tanan. Mahimo kang mopahuway sa presensya sa Dios ug moduol kaniya nga matinud-anon.',
                'prayer' => 'Ginoo, pabilin uban kanako ug hatagi ko sa imong kalinaw ug kusog alang niining taknaa. Amen.',
                'next_step' => 'Hinaya ang imong pagginhawa ug basaha ang mosunod nga mga pulong sa makausa pa, nga walay pagdali.',
                'passages' => $passages,
                'ai' => false,
            ];
        }

        return [
            'type' => 'reflection',
            'message' => 'I am sorry this feels heavy right now. You do not have to resolve everything at once. You can rest for a moment in God’s presence and speak honestly to him.',
            'prayer' => 'Lord, remain with me and give me your peace and strength for this moment. Amen.',
            'next_step' => 'Slow your breathing and read the passages below once more without rushing.',
            'passages' => $passages,
            'ai' => false,
        ];
    }
}

