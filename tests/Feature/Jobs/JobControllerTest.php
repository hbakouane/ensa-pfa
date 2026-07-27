<?php

namespace Tests\Feature\Jobs;

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

        // Reset cached permissions
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->company = Company::factory()->create(['plan_slug' => 'starter']);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);

        setPermissionsTeamId($this->company->id);
        $this->user->assignRole('owner');

        // Create default pipeline stages for the company
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

    public function test_user_can_create_a_job(): void
    {
        $jobData = [
            'title' => 'Senior Laravel Developer',
            'description' => 'We are looking for an experienced Laravel developer.',
            'employment_type' => 'full_time',
            'experience_level' => 'senior',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'salary_currency' => 'USD',
            'show_salary' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('jobs.store'), $jobData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jobs', [
            'title' => 'Senior Laravel Developer',
            'company_id' => $this->company->id,
            'status' => 'draft',
            'employment_type' => 'full_time',
        ]);
    }

    public function test_creating_a_job_requires_title(): void
    {
        $response = $this->actingAs($this->user)->post(route('jobs.store'), [
            'title' => '',
            'description' => 'Some description',
            'employment_type' => 'full_time',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_creating_a_job_requires_description(): void
    {
        $response = $this->actingAs($this->user)->post(route('jobs.store'), [
            'title' => 'Some Title',
            'description' => '',
            'employment_type' => 'full_time',
        ]);

        $response->assertSessionHasErrors('description');
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

    public function test_user_can_update_a_job(): void
    {
        $job = JobPosting::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('jobs.update', $job), [
            'title' => 'Updated Title',
            'description' => 'Updated description for the job.',
            'employment_type' => 'contract',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $job->refresh();
        $this->assertEquals('Updated Title', $job->title);
        $this->assertEquals('contract', $job->employment_type);
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

    public function test_user_can_view_create_job_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('jobs.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Jobs/Create')
        );
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
}
