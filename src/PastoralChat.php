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

        if ($this->knowledge->isKnowledgeQuery($message)) {
            return $this->answerKnowledge($message, $language, $history);
        }

        $excludedIds = [];
        foreach ($history as $turn) {
            foreach ($turn['passage_ids'] ?? [] as $id) {
                $excludedIds[] = (string) $id;
            }
        }

        $passages = $this->scripture->find($message, $language, array_values(array_unique($excludedIds)));
        $allowedIds = array_column($passages, 'id');

        try {
            $response = $this->openAI->reflect($message, $language, $passages, $history);
            $selectedIds = array_values(array_intersect($allowedIds, $response['passage_ids'] ?? []));
            $selected = array_values(array_filter(
                $passages,
                static fn (array $passage): bool => in_array($passage['id'], $selectedIds, true)
            ));

            return [
                'type' => 'reflection',
                'message' => (string) $response['reflection'],
                'prayer' => (string) $response['prayer'],
                'next_step' => (string) $response['next_step'],
                'passages' => $selected ?: $passages,
                'ai' => true,
            ];
        } catch (Throwable) {
            return $this->fallback($language, $passages);
        }
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

