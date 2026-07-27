<?php

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterCompanyAction
{
    public function execute(array $companyData, array $userData): array
    {
        return DB::transaction(function () use ($companyData, $userData) {
            $company = Company::create([
                'name' => $companyData['company_name'],
                'slug' => Str::slug($companyData['company_name']),
                'industry' => $companyData['industry'] ?? null,
                'size' => $companyData['size'] ?? null,
                'plan_slug' => 'free',
            ]);

            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'company_id' => $company->id,
                'type' => 'company',
            ]);

            setPermissionsTeamId($company->id);
            $user->assignRole('owner');

            // Create default pipeline stages
            foreach (config('recruiting.default_pipeline_stages') as $stage) {
                $company->pipelineStages()->create($stage);
            }

            return compact('company', 'user');
        });
    }
}
