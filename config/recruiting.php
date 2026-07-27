<?php

return [

    'default_pipeline_stages' => [
        ['name' => 'Candidature', 'slug' => 'applied', 'color' => '#6B7280', 'position' => 0],
        ['name' => 'Présélection', 'slug' => 'screening', 'color' => '#3B82F6', 'position' => 1],
        ['name' => 'Entretien', 'slug' => 'interview', 'color' => '#8B5CF6', 'position' => 2],
        ['name' => 'Évaluation', 'slug' => 'assessment', 'color' => '#F59E0B', 'position' => 3],
        ['name' => 'Offre', 'slug' => 'offer', 'color' => '#10B981', 'position' => 4],
        ['name' => 'Embauché', 'slug' => 'hired', 'color' => '#059669', 'position' => 5],
    ],

    'job_statuses' => ['draft', 'published', 'closed', 'archived'],

    'employment_types' => [
        'full_time' => 'Temps plein',
        'part_time' => 'Temps partiel',
        'contract' => 'Contrat',
        'temporary' => 'Temporaire',
        'internship' => 'Stage',
        'freelance' => 'Freelance',
    ],

    'experience_levels' => [
        'entry' => 'Débutant',
        'mid' => 'Intermédiaire',
        'senior' => 'Senior',
        'lead' => 'Lead',
        'executive' => 'Dirigeant',
    ],

    'currency' => env('RECRUITING_CURRENCY', 'USD'),

    'plans' => [
        'free' => [
            'name' => 'Gratuit',
            'max_jobs' => 2,
            'max_candidates' => 50,
            'max_users' => 2,
            'ai_parses_per_month' => 10,
            'price_monthly' => 0,
        ],
        'starter' => [
            'name' => 'Starter',
            'max_jobs' => 10,
            'max_candidates' => 500,
            'max_users' => 5,
            'ai_parses_per_month' => 100,
            'price_monthly' => 49,
            'stripe_monthly_price_id' => env('STRIPE_STARTER_MONTHLY_PRICE_ID'),
            'stripe_yearly_price_id' => env('STRIPE_STARTER_YEARLY_PRICE_ID'),
        ],
        'pro' => [
            'name' => 'Pro',
            'max_jobs' => 50,
            'max_candidates' => 5000,
            'max_users' => 25,
            'ai_parses_per_month' => 1000,
            'price_monthly' => 149,
            'stripe_monthly_price_id' => env('STRIPE_PRO_MONTHLY_PRICE_ID'),
            'stripe_yearly_price_id' => env('STRIPE_PRO_YEARLY_PRICE_ID'),
        ],
        'enterprise' => [
            'name' => 'Entreprise',
            'max_jobs' => -1, // unlimited
            'max_candidates' => -1,
            'max_users' => -1,
            'ai_parses_per_month' => -1,
            'price_monthly' => 399,
            'stripe_monthly_price_id' => env('STRIPE_ENTERPRISE_MONTHLY_PRICE_ID'),
            'stripe_yearly_price_id' => env('STRIPE_ENTERPRISE_YEARLY_PRICE_ID'),
        ],
    ],

    'offer' => [
        'expiry_days' => 7,
        'placeholders' => [
            '{{candidate_name}}',
            '{{position_title}}',
            '{{salary}}',
            '{{start_date}}',
            '{{company_name}}',
            '{{hiring_manager}}',
        ],
    ],
];
