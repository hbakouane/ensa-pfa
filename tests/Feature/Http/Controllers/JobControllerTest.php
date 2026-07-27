<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\JobPosting;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class JobControllerTest extends TestCase
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
        foreach (config('recruiting.default_pipeline_stages') as $stage) {
            $this->company->pipelineStages()->create($stage);
        }
    }

    public function test_authenticated_user_can_list_jobs(): void
    {
        JobPosting::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('jobs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Jobs/Index')
            ->has('jobs.data', 3)
        );
    }

    public function test_unauthenticated_user_cannot_list_jobs(): void
    {
        $response = $this->get(route('jobs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('jobs.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Jobs/Create'));
    }

    public function test_user_can_create_a_job(): void
    {
        $response = $this->actingAs($this->user)->post(route('jobs.store'), [
            'title' => 'Senior Laravel Developer',
            'description' => 'We are looking for an experienced Laravel developer.',
            'employment_type' => 'full_time',
            'experience_level' => 'senior',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'salary_currency' => 'USD',
            'show_salary' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('jobs', [
            'title' => 'Senior Laravel Developer',
            'company_id' => $this->company->id,
            'status' => 'draft',
        ]);
    }

    public function test_creating_job_requires_title_and_description(): void
    {
        $response = $this->actingAs($this->user)->post(route('jobs.store'), [
            'title' => '',
            'description' => '',
            'employment_type' => 'full_time',
        ]);

        $response->assertSessionHasErrors(['title', 'description']);
    }

    public function test_user_can_view_a_single_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('jobs.show', $job));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Jobs/Show')
            ->has('job')
        );
    }

    public function test_user_can_update_a_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('jobs.update', $job), [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'employment_type' => 'contract',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $job->refresh();
        $this->assertEquals('Updated Title', $job->title);
    }

    public function test_user_can_publish_a_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->patch(route('jobs.publish', $job));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $job->refresh();
        $this->assertEquals('published', $job->status);
    }

    public function test_user_can_close_a_job(): void
    {
        $job = JobPosting::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('jobs.close', $job));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $job->refresh();
        $this->assertEquals('closed', $job->status);
    }

    public function test_user_can_archive_a_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->user)->patch(route('jobs.archive', $job));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $job->refresh();
        $this->assertEquals('archived', $job->status);
    }

    public function test_user_can_delete_a_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('jobs.destroy', $job));

        $response->assertRedirect(route('jobs.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    public function test_unauthenticated_user_cannot_create_job(): void
    {
        $response = $this->post(route('jobs.store'), [
            'title' => 'Test',
            'description' => 'Test',
            'employment_type' => 'full_time',
        ]);

        $response->assertRedirect(route('login'));
    }
}
