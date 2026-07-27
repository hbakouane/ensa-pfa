<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PlanSeeder::class);
    }

    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get(route('company.register'));

        $response->assertStatus(200);
    }

    public function test_new_company_and_user_can_register(): void
    {
        $response = $this->post(route('company.register.store'), [
            'company_name' => 'Acme Corporation',
            'industry' => 'Technology',
            'size' => '11-50',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Assert company was created
        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corporation',
            'industry' => 'Technology',
            'size' => '11-50',
            'plan_slug' => 'free',
        ]);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'type' => 'company',
        ]);

        // Assert user was assigned owner role
        $user = User::where('email', 'john@acme.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('owner'));

        // Assert user is logged in
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_creates_default_pipeline_stages(): void
    {
        $this->post(route('company.register.store'), [
            'company_name' => 'Pipeline Corp',
            'name' => 'Jane Doe',
            'email' => 'jane@pipeline.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $company = Company::where('name', 'Pipeline Corp')->first();
        $this->assertNotNull($company);

        $stages = $company->pipelineStages;
        $this->assertGreaterThan(0, $stages->count());
        $this->assertEquals('Applied', $stages->first()->name);
    }

    public function test_registration_requires_company_name(): void
    {
        $response = $this->post(route('company.register.store'), [
            'company_name' => '',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('company_name');
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post(route('company.register.store'), [
            'company_name' => 'Acme Corporation',
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_unique_email(): void
    {
        // Create an existing user
        User::factory()->create(['email' => 'john@acme.com']);

        $response = $this->post(route('company.register.store'), [
            'company_name' => 'Acme Corporation',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post(route('company.register.store'), [
            'company_name' => 'Acme Corporation',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_requires_name(): void
    {
        $response = $this->post(route('company.register.store'), [
            'company_name' => 'Acme Corporation',
            'name' => '',
            'email' => 'john@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
