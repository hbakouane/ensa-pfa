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
                'name' => 'Gratuit',
                'slug' => 'free',
                'description' => 'Idéal pour les petites équipes qui débutent dans le recrutement.',
                'max_jobs' => 2,
                'max_candidates' => 50,
                'max_users' => 2,
                'ai_parses_per_month' => 10,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 0,
                'features' => json_encode([
                    'Publication d\'offres basique',
                    'Gestion des candidats',
                    'Pipeline Kanban',
                    '10 analyses de CV par IA/mois',
                ]),
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Pour les équipes en croissance qui ont besoin de plus de puissance.',
                'max_jobs' => 10,
                'max_candidates' => 500,
                'max_users' => 5,
                'ai_parses_per_month' => 100,
                'price_monthly' => 49,
                'price_yearly' => 470,
                'sort_order' => 1,
                'features' => json_encode([
                    'Tout ce qui est inclus dans Gratuit',
                    'Jusqu\'à 10 offres actives',
                    '100 analyses de CV par IA/mois',
                    'Planification d\'entretiens',
                    'Gestion des offres',
                ]),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Pour les équipes établies avec un volume de recrutement élevé.',
                'max_jobs' => 50,
                'max_candidates' => 5000,
                'max_users' => 25,
                'ai_parses_per_month' => 1000,
                'price_monthly' => 149,
                'price_yearly' => 1430,
                'sort_order' => 2,
                'features' => json_encode([
                    'Tout ce qui est inclus dans Starter',
                    'Jusqu\'à 50 offres actives',
                    '1 000 analyses de CV par IA/mois',
                    'Analytiques avancées',
                    'Collaboration d\'équipe',
                    'Étapes de pipeline personnalisées',
                ]),
            ],
            [
                'name' => 'Entreprise',
                'slug' => 'enterprise',
                'description' => 'Tout en illimité pour les grandes organisations.',
                'max_jobs' => -1,
                'max_candidates' => -1,
                'max_users' => -1,
                'ai_parses_per_month' => -1,
                'price_monthly' => 399,
                'price_yearly' => 3830,
                'sort_order' => 3,
                'features' => json_encode([
                    'Tout ce qui est inclus dans Pro',
                    'Offres et candidats illimités',
                    'Analyses IA illimitées',
                    'Support prioritaire',
                    'Intégrations personnalisées',
                    'SSO et sécurité avancée',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
