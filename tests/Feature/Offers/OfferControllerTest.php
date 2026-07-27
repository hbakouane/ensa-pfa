<?php

namespace Tests\Feature\Offers;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Offer;
use App\Models\PipelineStage;
use App\Models\User;
use App\Notifications\OfferSentNotification;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $user;

    private JobPosting $job;

    private Candidate $candidate;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->company = Company::factory()->create(['plan_slug' => 'starter']);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'company',
        ]);

        setPermissionsTeamId($this->company->id);
        $this->user->assignRole('owner');

        $stage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Offer',
            'slug' => 'offer',
            'position' => 4,
        ]);

        $this->job = JobPosting::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $this->candidate = Candidate::factory()->create();

        $this->application = Application::factory()->create([
            'company_id' => $this->company->id,
            'job_id' => $this->job->id,
            'candidate_id' => $this->candidate->id,
            'pipeline_stage_id' => $stage->id,
        ]);
    }

    public function test_user_can_create_an_offer(): void
    {
        $response = $this->actingAs($this->user)->post(route('offers.store'), [
            'application_id' => $this->application->id,
            'salary' => 120000,
            'salary_currency' => 'USD',
            'salary_period' => 'yearly',
            'start_date' => now()->addDays(30)->format('Y-m-d'),
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
            'content' => 'We are pleased to offer you the position...',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('offers', [
            'application_id' => $this->application->id,
            'company_id' => $this->company->id,
            'salary' => 120000,
            'status' => 'draft',
        ]);
    }

    public function test_creating_offer_requires_salary(): void
    {
        $response = $this->actingAs($this->user)->post(route('offers.store'), [
            'application_id' => $this->application->id,
            'salary' => '',
            'content' => 'Offer content...',
        ]);

        $response->assertSessionHasErrors('salary');
    }

    public function test_creating_offer_requires_content(): void
    {
        $response = $this->actingAs($this->user)->post(route('offers.store'), [
            'application_id' => $this->application->id,
            'salary' => 120000,
            'content' => '',
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_user_can_send_an_offer(): void
    {
        Notification::fake();

        $offer = Offer::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('offers.send', $offer));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $offer->refresh();
        $this->assertEquals('sent', $offer->status);
        $this->assertNotNull($offer->sent_at);

        // Verify notification was sent
        Notification::assertSentOnDemand(OfferSentNotification::class);
    }

    public function test_user_can_view_offer_details(): void
    {
        $offer = Offer::factory()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('offers.show', $offer));

        $response->assertStatus(200);
    }

    public function test_public_offer_response_page_loads(): void
    {
        $offer = Offer::factory()->sent()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('offers.respond', $offer->token));

        $response->assertStatus(200);
    }

    public function test_candidate_can_accept_offer(): void
    {
        $offer = Offer::factory()->sent()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addDays(7),
        ]);

        $response = $this->post(route('offers.respond.submit', $offer->token), [
            'decision' => 'accepted',
        ]);

        $response->assertRedirect();

        $offer->refresh();
        $this->assertEquals('accepted', $offer->status);
        $this->assertNotNull($offer->responded_at);
    }

    public function test_candidate_can_decline_offer(): void
    {
        $offer = Offer::factory()->sent()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addDays(7),
        ]);

        $response = $this->post(route('offers.respond.submit', $offer->token), [
            'decision' => 'declined',
        ]);

        $response->assertRedirect();

        $offer->refresh();
        $this->assertEquals('declined', $offer->status);
        $this->assertNotNull($offer->responded_at);
    }

    public function test_expired_offer_cannot_be_responded_to(): void
    {
        $offer = Offer::factory()->sent()->create([
            'company_id' => $this->company->id,
            'application_id' => $this->application->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->subDays(1),
        ]);

        $response = $this->post(route('offers.respond.submit', $offer->token), [
            'decision' => 'accepted',
        ]);

        // Should redirect back with error (offer can't be responded to)
        $response->assertRedirect();

        $offer->refresh();
        $this->assertEquals('sent', $offer->status);
        $this->assertNull($offer->responded_at);
    }
}
