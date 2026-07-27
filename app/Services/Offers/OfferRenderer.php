<?php

namespace App\Services\Offers;

class OfferRenderer
{
    /**
     * Replace template placeholders with actual values.
     *
     * Supported placeholders:
     * {{candidate_name}}, {{position_title}}, {{salary}},
     * {{start_date}}, {{company_name}}, {{hiring_manager}}
     */
    public function render(string $templateContent, array $values): string
    {
        $placeholders = [
            '{{candidate_name}}' => $values['candidate_name'] ?? '',
            '{{position_title}}' => $values['position_title'] ?? '',
            '{{salary}}' => $values['salary'] ?? '',
            '{{start_date}}' => $values['start_date'] ?? '',
            '{{company_name}}' => $values['company_name'] ?? '',
            '{{hiring_manager}}' => $values['hiring_manager'] ?? '',
        ];

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $templateContent,
        );
    }
}
