<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->company = Company::factory()->create(['plan_slug' => 'starter']);
        $this->user = User::factory()->create(['company_id' => $this->company->id, 'type' => 'company']);
        setPermissionsTeamId($this->company->id);
        $this->user->assignRole('owner');
    }

    public function test_user_with_permission_can_view_analytics(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Analytics/Index')
            ->has('overview')
        );
    }

    public function test_user_can_get_time_to_hire_data(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analytics.time-to-hire'));
        $response->assertStatus(200);
    }

    public function test_user_can_get_pipeline_conversion_data(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analytics.pipeline-conversion'));
        $response->assertStatus(200);
    }

    public function test_user_can_get_sources_data(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analytics.sources'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['sources']);
    }

    public function test_user_can_get_team_performance_data(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('analytics.team'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['team']);
    }

    public function test_user_without_permission_cannot_view_analytics(): void
    {
        $restrictedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);
        setPermissionsTeamId($this->company->id);
        $restrictedUser->assignRole('interviewer');

        $response = $this->actingAs($restrictedUser)->get(route('analytics.index'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_analytics(): void
    {
        $response = $this->get(route('analytics.index'));
        $response->assertRedirect(route('login'));
    }
}
