<?php

namespace App\Services\AI\Schemas;

class ScoreSchema
{
    /**
     * Return the JSON Schema definition for candidate scoring output.
     */
    public static function schema(): array
    {
        return [
            'name' => 'candidate_score',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'overall_score' => [
                        'type' => 'integer',
                        'description' => 'Overall candidate score from 0 to 100.',
                    ],
                    'skills_score' => [
                        'type' => 'integer',
                        'description' => 'Skills match score from 0 to 100.',
                    ],
                    'experience_score' => [
                        'type' => 'integer',
                        'description' => 'Experience relevance score from 0 to 100.',
                    ],
                    'education_score' => [
                        'type' => 'integer',
                        'description' => 'Education fit score from 0 to 100.',
                    ],
                    'fit_score' => [
                        'type' => 'integer',
                        'description' => 'Overall culture and role fit score from 0 to 100.',
                    ],
                    'strengths' => [
                        'type' => 'array',
                        'description' => 'List of candidate strengths relative to the role.',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    'weaknesses' => [
                        'type' => 'array',
                        'description' => 'List of candidate weaknesses or gaps relative to the role.',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    'recommendation' => [
                        'type' => 'string',
                        'description' => 'Hiring recommendation: strong_yes, yes, maybe, no, or strong_no.',
                        'enum' => ['strong_yes', 'yes', 'maybe', 'no', 'strong_no'],
                    ],
                    'reasoning' => [
                        'type' => 'string',
                        'description' => 'Detailed reasoning for the scores and recommendation.',
                    ],
                ],
                'required' => [
                    'overall_score',
                    'skills_score',
                    'experience_score',
                    'education_score',
                    'fit_score',
                    'strengths',
                    'weaknesses',
                    'recommendation',
                    'reasoning',
                ],
                'additionalProperties' => false,
            ],
        ];
    }
}
