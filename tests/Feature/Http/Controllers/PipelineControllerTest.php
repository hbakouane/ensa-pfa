<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PipelineControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private JobPosting $job;
    private PipelineStage $stage1;
    private PipelineStage $stage2;

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

        $this->stage1 = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Applied',
            'slug' => 'applied',
            'position' => 1,
        ]);

        $this->stage2 = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Interview',
            'slug' => 'interview',
            'position' => 2,
        ]);

        $this->job = JobPosting::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_view_pipeline(): void
    {
        $candidate = Candidate::factory()->create();
        Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $this->job->id,
            'candidate_id' => $candidate->id,
            'pipeline_stage_id' => $this->stage1->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('pipeline.show', $this->job));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Pipeline/Show')
            ->has('job')
            ->has('stages')
            ->has('applications')
        );
    }

    public function test_user_can_move_application_to_different_stage(): void
    {
        $candidate = Candidate::factory()->create();
        $application = Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $this->job->id,
            'candidate_id' => $candidate->id,
            'pipeline_stage_id' => $this->stage1->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('pipeline.move', $application), [
            'stage_id' => $this->stage2->id,
            'position' => 0,
        ]);

        $response->assertStatus(200);
        $application->refresh();
        $this->assertEquals($this->stage2->id, $application->pipeline_stage_id);
    }

    public function test_moving_application_requires_valid_stage(): void
    {
        $candidate = Candidate::factory()->create();
        $application = Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $this->job->id,
            'candidate_id' => $candidate->id,
            'pipeline_stage_id' => $this->stage1->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('pipeline.move', $application), [
            'stage_id' => 999999,
            'position' => 0,
        ]);

        $response->assertSessionHasErrors('stage_id');
    }

    public function test_user_can_reject_application(): void
    {
        $candidate = Candidate::factory()->create();
        $application = Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $this->job->id,
            'candidate_id' => $candidate->id,
            'pipeline_stage_id' => $this->stage1->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('pipeline.reject', $application), []);

        $response->assertStatus(200);
        $application->refresh();
        $this->assertEquals('rejected', $application->status);
    }

    public function test_unauthenticated_user_cannot_view_pipeline(): void
    {
        $response = $this->get(route('pipeline.show', $this->job));
        $response->assertRedirect(route('login'));
    }
}
