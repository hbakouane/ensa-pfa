<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BillingControllerTest extends TestCase
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

    public function test_user_without_permission_cannot_access_billing(): void
    {
        $restrictedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);
        setPermissionsTeamId($this->company->id);
        $restrictedUser->assignRole('recruiter');

        $response = $this->actingAs($restrictedUser)->get(route('billing.index'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_billing(): void
    {
        $response = $this->get(route('billing.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_subscribe_requires_plan_slug_and_payment_method(): void
    {
        $response = $this->actingAs($this->user)->post(route('billing.subscribe'), [
            'plan_slug' => '',
            'payment_method' => '',
        ]);

        $response->assertSessionHasErrors(['plan_slug', 'payment_method']);
    }

    public function test_change_plan_requires_valid_plan_slug(): void
    {
        $response = $this->actingAs($this->user)->patch(route('billing.change-plan'), [
            'new_plan_slug' => 'nonexistent-plan',
        ]);

        $response->assertSessionHasErrors('new_plan_slug');
    }

    public function test_recruiter_cannot_cancel_subscription(): void
    {
        $restrictedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);
        setPermissionsTeamId($this->company->id);
        $restrictedUser->assignRole('recruiter');

        $response = $this->actingAs($restrictedUser)->post(route('billing.cancel'));
        $response->assertStatus(403);
    }

    public function test_recruiter_cannot_resume_subscription(): void
    {
        $restrictedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);
        setPermissionsTeamId($this->company->id);
        $restrictedUser->assignRole('recruiter');

        $response = $this->actingAs($restrictedUser)->post(route('billing.resume'));
        $response->assertStatus(403);
    }
}
