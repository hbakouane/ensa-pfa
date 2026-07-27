<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Company
            'company.update', 'company.manage_billing', 'company.manage_settings',
            // Users
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.invite',
            // Jobs
            'jobs.view', 'jobs.create', 'jobs.update', 'jobs.delete', 'jobs.publish',
            // Candidates
            'candidates.view', 'candidates.create', 'candidates.update', 'candidates.delete',
            // Applications
            'applications.view', 'applications.create', 'applications.update', 'applications.move_stage',
            'applications.reject',
            // Interviews
            'interviews.view', 'interviews.create', 'interviews.update', 'interviews.delete',
            'interviews.submit_scorecard',
            // Offers
            'offers.view', 'offers.create', 'offers.update', 'offers.send', 'offers.approve',
            // Analytics
            'analytics.view', 'analytics.export',
            // AI
            'ai.parse_resume', 'ai.score_candidates', 'ai.generate_summary',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all()->filter(fn ($p) => $p->name !== 'company.manage_billing'));

        $recruiter = Role::firstOrCreate(['name' => 'recruiter', 'guard_name' => 'web']);
        $recruiter->givePermissionTo([
            'jobs.view', 'jobs.create', 'jobs.update', 'jobs.publish',
            'candidates.view', 'candidates.create', 'candidates.update',
            'applications.view', 'applications.create', 'applications.update',
            'applications.move_stage', 'applications.reject',
            'interviews.view', 'interviews.create', 'interviews.update',
            'interviews.submit_scorecard',
            'offers.view', 'offers.create', 'offers.update', 'offers.send',
            'analytics.view',
            'ai.parse_resume', 'ai.score_candidates', 'ai.generate_summary',
        ]);

        $hiringManager = Role::firstOrCreate(['name' => 'hiring_manager', 'guard_name' => 'web']);
        $hiringManager->givePermissionTo([
            'jobs.view', 'jobs.create', 'jobs.update',
            'candidates.view',
            'applications.view', 'applications.move_stage',
            'interviews.view', 'interviews.create', 'interviews.submit_scorecard',
            'offers.view', 'offers.approve',
            'analytics.view',
        ]);

        $interviewer = Role::firstOrCreate(['name' => 'interviewer', 'guard_name' => 'web']);
        $interviewer->givePermissionTo([
            'candidates.view',
            'applications.view',
            'interviews.view', 'interviews.submit_scorecard',
        ]);
    }
}
