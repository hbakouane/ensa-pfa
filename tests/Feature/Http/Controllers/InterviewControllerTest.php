<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InterviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private Application $application;

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

        $stage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Interview',
            'slug' => 'interview',
            'position' => 2,
        ]);

        $job = JobPosting::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $candidate = Candidate::factory()->create();

        $this->application = Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'pipeline_stage_id' => $stage->id,
        ]);
    }

    public function test_user_can_list_interviews(): void
    {
        Interview::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('interviews.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Interviews/Index')
            ->has('interviews.data', 2)
        );
    }

    public function test_user_can_schedule_an_interview(): void
    {
        $response = $this->actingAs($this->user)->post(route('interviews.store'), [
            'application_id' => $this->application->id,
            'title' => 'Technical Interview',
            'type' => 'technical',
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'location' => 'Conference Room A',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('interviews', [
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'title' => 'Technical Interview',
            'type' => 'technical',
        ]);
    }

    public function test_scheduling_interview_requires_title_and_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('interviews.store'), [
            'application_id' => $this->application->id,
            'title' => '',
            'type' => '',
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors(['title', 'type']);
    }

    public function test_user_can_view_interview_details(): void
    {
        $interview = Interview::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('interviews.show', $interview));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Interviews/Show')
            ->has('interview')
        );
    }

    public function test_user_can_update_an_interview(): void
    {
        $interview = Interview::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('interviews.update', $interview), [
            'title' => 'Updated Interview Title',
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $interview->refresh();
        $this->assertEquals('Updated Interview Title', $interview->title);
        $this->assertEquals('confirmed', $interview->status);
    }

    public function test_user_can_cancel_an_interview(): void
    {
        $interview = Interview::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('interviews.destroy', $interview));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_user_can_submit_scorecard(): void
    {
        $interview = Interview::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('scorecards.store', $interview), [
            'overall_rating' => 4,
            'recommendation' => 'yes',
            'strengths' => 'Great communication skills',
            'concerns' => 'Limited experience with microservices',
            'notes' => 'Overall positive impression',
            'criteria' => [
                ['name' => 'Technical Skills', 'rating' => 4, 'notes' => 'Strong'],
                ['name' => 'Communication', 'rating' => 5, 'notes' => 'Excellent'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('interview_scorecards', [
            'interview_id' => $interview->id,
            'user_id' => $this->user->id,
            'overall_rating' => 4,
            'recommendation' => 'yes',
        ]);
    }

    public function test_scorecard_requires_rating_and_recommendation(): void
    {
        $interview = Interview::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('scorecards.store', $interview), [
            'overall_rating' => '',
            'recommendation' => '',
        ]);

        $response->assertSessionHasErrors(['overall_rating', 'recommendation']);
    }

    public function test_unauthenticated_user_cannot_list_interviews(): void
    {
        $response = $this->get(route('interviews.index'));
        $response->assertRedirect(route('login'));
    }
}
