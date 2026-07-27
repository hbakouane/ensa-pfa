<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for small teams getting started with recruiting.',
                'max_jobs' => 2,
                'max_candidates' => 50,
                'max_users' => 2,
                'ai_parses_per_month' => 10,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 0,
                'features' => json_encode([
                    'Basic job posting',
                    'Candidate management',
                    'Kanban pipeline',
                    '10 AI resume parses/month',
                ]),
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For growing teams that need more power.',
                'max_jobs' => 10,
                'max_candidates' => 500,
                'max_users' => 5,
                'ai_parses_per_month' => 100,
                'price_monthly' => 49,
                'price_yearly' => 470,
                'sort_order' => 1,
                'features' => json_encode([
                    'Everything in Free',
                    'Up to 10 active jobs',
                    '100 AI resume parses/month',
                    'Interview scheduling',
                    'Offer management',
                ]),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For established teams with high-volume hiring.',
                'max_jobs' => 50,
                'max_candidates' => 5000,
                'max_users' => 25,
                'ai_parses_per_month' => 1000,
                'price_monthly' => 149,
                'price_yearly' => 1430,
                'sort_order' => 2,
                'features' => json_encode([
                    'Everything in Starter',
                    'Up to 50 active jobs',
                    '1,000 AI resume parses/month',
                    'Advanced analytics',
                    'Team collaboration',
                    'Custom pipeline stages',
                ]),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited everything for large organizations.',
                'max_jobs' => -1,
                'max_candidates' => -1,
                'max_users' => -1,
                'ai_parses_per_month' => -1,
                'price_monthly' => 399,
                'price_yearly' => 3830,
                'sort_order' => 3,
                'features' => json_encode([
                    'Everything in Pro',
                    'Unlimited jobs & candidates',
                    'Unlimited AI parses',
                    'Priority support',
                    'Custom integrations',
                    'SSO & advanced security',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
