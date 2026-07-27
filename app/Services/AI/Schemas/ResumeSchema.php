<?php

namespace App\Services\AI\Schemas;

class ResumeSchema
{
    /**
     * Return the JSON Schema definition for structured resume parsing output.
     */
    public static function schema(): array
    {
        return [
            'name' => 'resume_data',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'first_name' => [
                        'type' => 'string',
                        'description' => 'The candidate\'s first name.',
                    ],
                    'last_name' => [
                        'type' => 'string',
                        'description' => 'The candidate\'s last name.',
                    ],
                    'email' => [
                        'type' => ['string', 'null'],
                        'description' => 'The candidate\'s email address.',
                    ],
                    'phone' => [
                        'type' => ['string', 'null'],
                        'description' => 'The candidate\'s phone number.',
                    ],
                    'headline' => [
                        'type' => ['string', 'null'],
                        'description' => 'A professional headline or title.',
                    ],
                    'summary' => [
                        'type' => 'string',
                        'description' => 'A brief professional summary of the candidate.',
                    ],
                    'location' => [
                        'type' => ['string', 'null'],
                        'description' => 'The candidate\'s location (city, state, country).',
                    ],
                    'linkedin_url' => [
                        'type' => ['string', 'null'],
                        'description' => 'The candidate\'s LinkedIn profile URL.',
                    ],
                    'skills' => [
                        'type' => 'array',
                        'description' => 'List of the candidate\'s skills.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => [
                                    'type' => 'string',
                                    'description' => 'The skill name.',
                                ],
                                'years_of_experience' => [
                                    'type' => ['integer', 'null'],
                                    'description' => 'Years of experience with this skill.',
                                ],
                                'proficiency' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'Proficiency level (beginner, intermediate, advanced, expert).',
                                ],
                            ],
                            'required' => ['name', 'years_of_experience', 'proficiency'],
                            'additionalProperties' => false,
                        ],
                    ],
                    'experiences' => [
                        'type' => 'array',
                        'description' => 'Work experience entries.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'company_name' => [
                                    'type' => 'string',
                                    'description' => 'The employer company name.',
                                ],
                                'title' => [
                                    'type' => 'string',
                                    'description' => 'The job title held.',
                                ],
                                'description' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'Description of responsibilities and achievements.',
                                ],
                                'start_date' => [
                                    'type' => 'string',
                                    'description' => 'Start date in YYYY-MM-DD format.',
                                ],
                                'end_date' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'End date in YYYY-MM-DD format, or null if current.',
                                ],
                                'is_current' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether this is the current position.',
                                ],
                            ],
                            'required' => ['company_name', 'title', 'description', 'start_date', 'end_date', 'is_current'],
                            'additionalProperties' => false,
                        ],
                    ],
                    'educations' => [
                        'type' => 'array',
                        'description' => 'Education entries.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'institution' => [
                                    'type' => 'string',
                                    'description' => 'The educational institution name.',
                                ],
                                'degree' => [
                                    'type' => 'string',
                                    'description' => 'The degree obtained.',
                                ],
                                'field_of_study' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'The field of study or major.',
                                ],
                                'start_date' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'Start date in YYYY-MM-DD format.',
                                ],
                                'end_date' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'End date in YYYY-MM-DD format.',
                                ],
                                'gpa' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'GPA or grade achieved.',
                                ],
                            ],
                            'required' => ['institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'gpa'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
                'required' => [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'headline',
                    'summary',
                    'location',
                    'linkedin_url',
                    'skills',
                    'experiences',
                    'educations',
                ],
                'additionalProperties' => false,
            ],
        ];
    }
}
