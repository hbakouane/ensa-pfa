<?php

namespace Tests\Unit\Services\Billing;

use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Plan;
use App\Services\Billing\UsageLimitChecker;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageLimitCheckerTest extends TestCase
{
    use RefreshDatabase;

    private UsageLimitChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->checker = new UsageLimitChecker;
    }

    public function test_can_create_job_returns_true_when_under_limit(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'free']);

        // Free plan allows 2 active jobs, company has none
        $result = $this->checker->canCreateJob($company);

        $this->assertTrue($result);
    }

    public function test_can_create_job_returns_false_when_at_limit(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'free']);

        // Free plan max is 2 jobs. Create 2 active jobs.
        JobPosting::factory()->count(2)->create([
            'company_id' => $company->id,
            'status' => 'published',
        ]);

        $result = $this->checker->canCreateJob($company);

        $this->assertFalse($result);
    }

    public function test_can_create_job_does_not_count_closed_jobs(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'free']);

        // Create 2 closed jobs (should not count toward limit)
        JobPosting::factory()->count(2)->create([
            'company_id' => $company->id,
            'status' => 'closed',
        ]);

        $result = $this->checker->canCreateJob($company);

        $this->assertTrue($result);
    }

    public function test_unlimited_plan_always_returns_true(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'enterprise']);

        // Even with many active jobs, enterprise should return true
        JobPosting::factory()->count(100)->create([
            'company_id' => $company->id,
            'status' => 'published',
        ]);

        $result = $this->checker->canCreateJob($company);

        $this->assertTrue($result);
    }

    public function test_can_invite_user_returns_true_when_under_limit(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'free']);

        // Free plan allows 2 users, company has only the implicit owner (0 explicit)
        $result = $this->checker->canInviteUser($company);

        $this->assertTrue($result);
    }

    public function test_can_parse_resume_with_unlimited_plan(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'enterprise']);

        $result = $this->checker->canParseResume($company);

        $this->assertTrue($result);
    }

    public function test_get_usage_returns_correct_structure(): void
    {
        $company = Company::factory()->create(['plan_slug' => 'starter']);

        $result = $this->checker->getUsage($company);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('jobs', $result);
        $this->assertArrayHasKey('candidates', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('ai_parses', $result);

        $this->assertArrayHasKey('current', $result['jobs']);
        $this->assertArrayHasKey('max', $result['jobs']);
        $this->assertEquals(0, $result['jobs']['current']);
        $this->assertEquals(10, $result['jobs']['max']); // Starter plan max_jobs
    }
}
