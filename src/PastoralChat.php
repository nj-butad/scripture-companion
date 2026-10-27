<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use Throwable;

final class PastoralChat
{
    public function __construct(
        private readonly ScriptureRepository $scripture = new ScriptureRepository(),
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

        $passages = $this->scripture->find($message, $language);
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

