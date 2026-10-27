<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class SafetyService
{
    private const URGENT_PATTERNS = [
        '/\b(kill myself|end my life|suicide|want to die|hurt myself|self[- ]harm)\b/i',
        '/\b(patay(?:on)? nako akong kaugalingon|maghikog|gusto ko mamatay|pasakitan nako akong kaugalingon)\b/iu',
    ];

    public function needsUrgentResponse(string $message): bool
    {
        foreach (self::URGENT_PATTERNS as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    public function urgentResponse(string $language): array
    {
        if ($language === 'ceb') {
            return [
                'message' => 'Importante ka, ug dili kinahanglan nga ikaw ra ang moatubang niini. Kung anaa ka sa peligro karon, palihog tawag dayon sa lokal nga emergency services o duol sa usa ka kasaligan nga tawo nga makapabilin uban kanimo. Mahimo usab kang mokontak sa pari, pastor, doktor, o mental-health professional karon.',
                'next_step' => 'Makabalhin ka ba karon ngadto sa lugar nga adunay laing tawo ug isulti kanila nga kinahanglan nimo og tabang?',
            ];
        }

        return [
            'message' => 'You matter, and you do not have to face this alone. If you may be in immediate danger, contact local emergency services now or go to a trusted person who can stay with you. Please also contact a priest, pastor, doctor, or mental-health professional right away.',
            'next_step' => 'Can you move to a place with another person and tell them clearly that you need immediate support?',
        ];
    }
}

