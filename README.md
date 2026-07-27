# RecruitAI — AI-Powered Recruiting Platform

A mid-to-large Laravel SaaS application where companies post jobs, candidates apply with resumes, and AI handles resume parsing, candidate scoring, and match suggestions. Includes interview scheduling, Kanban pipeline management, offer letter generation, team collaboration, analytics, and Stripe billing.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11+ with Inertia.js |
| **Frontend** | Vue 3 + Inertia |
| **AI** | OpenAI API (GPT-4o) via Structured Outputs |
| **Auth** | Multi-tenant (column-based `company_id` scoping) + Spatie Permissions (teams mode) |
| **Database** | MySQL (SQLite for local dev) |
| **Queue** | Redis (3 queues: `default`, `ai`, `notifications`) |
| **Real-time** | Laravel Reverb + Echo |
| **Payments** | Stripe via Laravel Cashier |
| **Search** | Laravel Scout + Meilisearch |
| **PDF** | barryvdh/laravel-dompdf |
| **Storage** | S3 for resumes/files |

---

## Architecture Decisions

1. **Multi-tenancy**: Column-based with `company_id` + a `BelongsToCompany` trait that applies a global scope. Candidates are global (shared across companies via a pivot table), everything else is company-scoped.

2. **AI Service Layer**: `app/Services/AI/` namespace — `OpenAIClient` (retries, rate limits, token logging), `ResumeParser`, `CandidateScorer`, `CandidateSummarizer`, `InterviewQuestionGenerator`. All AI calls dispatched as queued jobs on the `ai` queue.

3. **Model naming**: `JobPosting` model (not `Job`) to avoid Laravel's queue Job class collision, with `protected $table = 'jobs'`.

4. **Kanban**: RESTful PATCH endpoints, `vuedraggable` on frontend, optimistic UI with server reconciliation + Echo real-time sync.

5. **Actions pattern**: Business logic encapsulated in single-purpose Action classes under `app/Actions/`, keeping controllers thin.

---

## Setup

```bash
# Clone and install
cd RecruitAI
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Configure your .env:
# - Database credentials (DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# - OpenAI API key (OPENAI_API_KEY)
# - Stripe keys (STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET)
# - Meilisearch (MEILISEARCH_HOST, MEILISEARCH_KEY)
# - Redis for queues
# - S3 credentials for file storage (AWS_*)

# Database
php artisan migrate
php artisan db:seed

# Build frontend
npm run dev

# Start queue workers (separate terminals)
php artisan queue:work --queue=default
php artisan queue:work --queue=ai
php artisan queue:work --queue=notifications

# Start WebSocket server
php artisan reverb:start

# Start Meilisearch (if using search)
# meilisearch --master-key=your_key

# Scout index
php artisan scout:import "App\Models\JobPosting"
php artisan scout:import "App\Models\Candidate"
```

---

## Packages

| Package | Purpose |
|---------|---------|
| `laravel/breeze` | Auth scaffolding (Inertia + Vue 3) |
| `spatie/laravel-permission` | Roles & permissions with team support |
| `laravel/cashier` | Stripe subscriptions |
| `laravel/scout` + `meilisearch/meilisearch-php` | Full-text search |
| `laravel/reverb` | WebSocket server |
| `barryvdh/laravel-dompdf` | PDF generation |
| `smalot/pdfparser` | Resume text extraction |
| `league/flysystem-aws-s3-v3` | S3 storage |
| `vuedraggable@next` (npm) | Kanban drag-and-drop |
| `@tiptap/vue-3` (npm) | Rich text editor |
| `chart.js` + `vue-chartjs` (npm) | Analytics charts |
| `pinia` (npm) | State management |

---

## Phased Implementation

### Phase 1: Foundation

**Goal**: Project scaffolding, auth, multi-tenancy, layout shell.

**What was built**:
- Laravel project with Breeze (Vue + Inertia), all packages installed
- Config files: `config/ai.php` (OpenAI settings, rate limits, scoring weights), `config/recruiting.php` (pipeline stages, job statuses, employment types, plan definitions, offer placeholders)
- Migrations: `companies`, modified `users` (company_id, type, avatar_path), `company_invitations`, `plans`, Spatie permission tables
- Models: `Company` (with Billable), `User` (with HasRoles), `CompanyInvitation`, `Plan`
- `BelongsToCompany` trait with `CompanyScope` global scope — foundation of data isolation
- Middleware: `SetCurrentCompany` (sets tenant context), `EnsureCompanySubscription` (checks billing status)
- Seeders: `RolesAndPermissionsSeeder` (owner, admin, recruiter, hiring_manager, interviewer roles with granular permissions), `PlanSeeder` (Free, Starter, Pro, Enterprise)
- Auth: `CompanyRegistrationController` (registers company + owner user), `InvitationController` (invite/accept team members), `RegisterCompanyAction`
- Layouts: `AppLayout.vue` (sidebar + top nav), `Sidebar.vue`, `TopNav.vue`, `PublicLayout.vue`, `FlashMessages.vue`, `Dashboard/Index.vue`

**Verification**: Register a company, login, see dashboard, invite a team member.

---

### Phase 2: Core Features

**Goal**: Jobs, candidates, applications, Kanban pipeline, public job board.

**What was built**:
- Migrations: `departments`, `locations`, `job_categories`, `jobs` (with full schema: salary, remote policy, status workflow), `job_skills`, `job_templates`, `candidates` (global), `candidate_skills`, `candidate_experiences`, `candidate_educations`, `candidate_company` (pivot), `tags`, `candidate_tag`, `pipeline_stages`, `applications` (with AI score fields), `rejection_reasons`, `application_stage_history`
- Models for all above with full relationships, casts, scopes
- `JobPosting` model with `$table = 'jobs'`, searchable, published/active/draft scopes
- `Candidate` model (global, not company-scoped), searchable, with full name accessor
- Controllers: `JobController` (CRUD + publish/close/archive), `JobBoardController` (public), `CandidateController` (CRUD), `CandidateTagController`, `ApplicationController`, `PipelineController` (Kanban move/reorder), `PipelineStageController`
- Actions: `CreateJobAction`, `PublishJobAction`, `CreateCandidateAction`, `CreateApplicationAction`, `MoveApplicationStageAction` (records history, fires events), `RejectApplicationAction`
- Vue pages: Jobs (Index, Create, Edit, Show), Candidates (Index, Show, Create), Pipeline/Show (Kanban board)
- Vue components: `KanbanBoard.vue`, `KanbanColumn.vue`, `KanbanCard.vue`, base UI components (Button, Modal, DataTable, Badge, Pagination, Input, Select, Textarea)
- Composable: `useKanban.js` — optimistic drag-and-drop with server reconciliation
- Public pages: `JobBoard.vue`, `JobDetail.vue`, `ApplicationForm.vue`

**Verification**: Create a job, post it, apply via public board, see application on Kanban, drag between stages.

---

### Phase 3: AI Integration

**Goal**: Resume parsing, candidate scoring, summaries, interview questions.

**What was built**:
- Migration: `ai_usage_logs` (tracks tokens, costs, status per AI call)
- `OpenAIClient` service — HTTP wrapper with retry logic, exponential backoff, rate limiting, token usage logging, Structured Outputs support
- JSON Schemas: `ResumeSchema` (structured resume data extraction), `ScoreSchema` (0-100 scoring with breakdown)
- `ResumeParser` — extracts text from PDF via smalot/pdfparser, sends to OpenAI for structured parsing
- `CandidateScorer` — compares candidate data vs job requirements, returns weighted 0-100 score with breakdown (skills, experience, education, fit)
- `CandidateSummarizer` — generates 2-3 paragraph professional summaries
- `InterviewQuestionGenerator` — generates tailored questions with category, difficulty, and evaluation criteria
- Queue jobs (all on `ai` queue): `ParseResumeJob`, `ScoreCandidateJob`, `BulkScoreCandidatesJob`, `GenerateCandidateSummaryJob`, `GenerateInterviewQuestionsJob`
- `AIController` — endpoints to trigger AI actions manually
- `ScoreDisplay.vue` — circular SVG score display with color coding (red/yellow/green)

**Verification**: Upload a resume, verify AI parses it, check candidate score, generate summary.

---

### Phase 4: Advanced Features

**Goal**: Interviews, scorecards, offers, collaboration, real-time.

**What was built**:
- Migrations: `interviews`, `interview_interviewers` (pivot with response tracking), `interview_scorecards`, `scorecard_criteria`, `offer_templates`, `offers` (with token-based public response), `offer_approvals`, `activities` (polymorphic activity log), `comments` (threaded, with @mentions), `media` (polymorphic file attachments)
- Models for all above with relationships
- Actions: `ScheduleInterviewAction`, `CreateOfferAction`, `SendOfferAction`, `GenerateOfferPdfAction`
- Services: `OfferRenderer` (placeholder replacement), `OfferPdfGenerator` (DomPDF to S3)
- Controllers: `InterviewController`, `ScorecardController`, `OfferController`, `OfferTemplateController`, `OfferResponseController` (public, tokenized), `CommentController`, `ActivityController`, `NotificationController`
- Notifications: `InterviewScheduledNotification`, `OfferSentNotification`, `OfferApprovalRequestedNotification`, `MentionedInCommentNotification`
- Events + Broadcasting: `ApplicationStageChanged`, `CommentAdded`, `InterviewScheduled` — broadcast on `private-company.{id}` channels
- Vue pages: Interviews (Index, Show), Offers (Index, Create, Show, Templates), OfferResponse (public)
- Vue components: `SchedulerModal.vue`, `ScorecardForm.vue`, `OfferPreview.vue`, `TemplateEditor.vue`, `CommentThread.vue`, `CommentInput.vue` (with @mentions), `ActivityFeed.vue`, `NotificationDropdown.vue`
- Composables: `useRealtime.js` (Echo subscriptions), `useNotifications.js` (notification management)

**Verification**: Schedule interview, fill scorecard, create and send offer, verify real-time notifications.

---

### Phase 5: Analytics & Billing

**Goal**: Analytics dashboard, Stripe subscription management.

**What was built**:
- Migrations: `source_tracking`, Cashier columns on `companies`, `subscriptions`, `subscription_items`
- Services: `AnalyticsService` (overview stats, date-filtered), `TimeToHireCalculator` (average/median/by-department/trends), `PipelineConversionCalculator` (stage-by-stage conversion and drop-off rates)
- Services: `SubscriptionService` (subscribe, change plan, cancel, resume via Cashier), `UsageLimitChecker` (enforces plan limits: jobs, candidates, users, AI parses)
- Middleware: `CheckUsageLimits` — blocks creation routes when plan limits exceeded
- Controllers: `AnalyticsController` (overview, time-to-hire, pipeline conversion, sources, team performance), `BillingController` (subscribe, change plan, cancel, resume, invoices, payment method)
- Vue pages: `Analytics/Index.vue`, `Settings/Billing.vue`
- Vue components: `MetricCard.vue`, `PipelineFunnel.vue` (CSS-based funnel), `TimeToHireChart.vue` (chart.js line), `SourceBreakdownChart.vue` (chart.js doughnut), `PricingTable.vue`, `SubscriptionManager.vue`, `UsageIndicator.vue` (color-coded progress bar)

**Verification**: Subscribe to a paid plan, verify limits enforced, check analytics dashboard.

---

### Phase 6: Polish

**Goal**: Search, emails, performance, testing.

**What was built**:
- Scout + Meilisearch configured, `JobPosting` and `Candidate` searchable
- `GlobalSearch.vue` — Cmd+K command palette with debounced search, grouped results, keyboard navigation
- `SearchController` — searches across jobs and candidates
- Blade email templates: interview-scheduled, offer-sent, offer-approval-requested, invitation
- Performance: `preventLazyLoading()` and `preventSilentlyDiscardingAttributes()` in dev
- Tests:
  - Unit: `ResumeParserTest`, `CandidateScorerTest`, `TimeToHireCalculatorTest`, `UsageLimitCheckerTest`
  - Feature: `CompanyRegistrationTest`, `JobControllerTest`, `PipelineControllerTest`, `OfferControllerTest`

**Verification**: Search for candidates/jobs, verify email delivery, run full test suite with `php artisan test`.

---

## Key Files

| File | Why It Matters |
|------|---------------|
| `app/Models/Concerns/BelongsToCompany.php` | Foundation of multi-tenancy — data isolation |
| `app/Models/Scopes/CompanyScope.php` | Global scope that filters all queries by company_id |
| `app/Http/Middleware/SetCurrentCompany.php` | Sets tenant context for every request |
| `app/Services/AI/OpenAIClient.php` | All AI features depend on this wrapper |
| `app/Services/AI/ResumeParser.php` | Core AI feature — structured resume extraction |
| `app/Services/AI/CandidateScorer.php` | AI scoring engine for candidate-job matching |
| `app/Actions/Applications/MoveApplicationStageAction.php` | Core pipeline logic — history, events |
| `resources/js/Components/Pipeline/KanbanBoard.vue` | Primary UI — drag-and-drop with real-time sync |
| `app/Services/Billing/UsageLimitChecker.php` | Enforces plan limits across the system |
| `config/recruiting.php` | Central configuration for pipeline, plans, job types |

---

## Roles & Permissions

| Role | Key Permissions |
|------|----------------|
| **Owner** | Full access including billing management |
| **Admin** | Everything except billing |
| **Recruiter** | Jobs, candidates, applications, interviews, offers, AI features |
| **Hiring Manager** | View jobs/candidates, move pipeline stages, interviews, approve offers |
| **Interviewer** | View candidates/applications, submit scorecards |

---

## API / Route Groups

| Prefix | Purpose |
|--------|---------|
| `/` | Public landing page |
| `/careers/{company:slug}` | Public job board per company |
| `/offers/respond/{token}` | Public offer response (tokenized) |
| `/dashboard` | Main dashboard |
| `/jobs` | Job management CRUD |
| `/candidates` | Candidate management |
| `/pipeline/{job}` | Kanban pipeline view |
| `/interviews` | Interview scheduling |
| `/offers` | Offer management |
| `/analytics` | Analytics dashboard |
| `/billing` | Subscription management |
| `/ai/*` | AI action endpoints |
| `/search` | Global search |
| `/notifications` | Notification management |

---

## Queue Configuration

```
default     — standard jobs (emails, general processing)
ai          — AI-intensive jobs (resume parsing, scoring, summarization)
notifications — notification dispatch
```

Run workers:
```bash
php artisan queue:work --queue=default,notifications
php artisan queue:work --queue=ai --timeout=120
```

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## License

Proprietary. All rights reserved.
