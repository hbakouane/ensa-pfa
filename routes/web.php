<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateTagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\JobBoardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferResponseController;
use App\Http\Controllers\OfferTemplateController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScorecardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\SearchController;
use App\Models\JobPosting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route model binding: 'job' parameter resolves to JobPosting model
Route::model('job', JobPosting::class);

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Company registration
Route::middleware('guest')->group(function () {
    Route::get('/register/company', [CompanyRegistrationController::class, 'create'])->name('company.register');
    Route::post('/register/company', [CompanyRegistrationController::class, 'store'])->name('company.register.store');
});

// Invitation acceptance
Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::post('/invitations/{token}/register', [InvitationController::class, 'register'])->name('invitations.register');

// Public Job Board
Route::prefix('careers/{company:slug}')->name('careers.')->group(function () {
    Route::get('/', [JobBoardController::class, 'index'])->name('index');
    Route::get('/{job:slug}', [JobBoardController::class, 'show'])->name('show');
    Route::get('/{job:slug}/apply', [JobBoardController::class, 'apply'])->name('apply');
    Route::post('/{job:slug}/apply', [JobBoardController::class, 'submitApplication'])->name('submit');
});

// Public Offer Response
Route::get('offers/respond/{token}', [OfferResponseController::class, 'show'])->name('offers.respond');
Route::post('offers/respond/{token}', [OfferResponseController::class, 'respond'])->name('offers.respond.submit');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Team invitations
    Route::post('/invitations', [InvitationController::class, 'store'])
        ->middleware('permission:users.invite')
        ->name('invitations.store');

    // Jobs
    Route::resource('jobs', JobController::class);
    Route::patch('jobs/{job}/publish', [JobController::class, 'publish'])->name('jobs.publish');
    Route::patch('jobs/{job}/close', [JobController::class, 'close'])->name('jobs.close');
    Route::patch('jobs/{job}/archive', [JobController::class, 'archive'])->name('jobs.archive');

    // Candidates
    Route::resource('candidates', CandidateController::class)->only(['index', 'create', 'store', 'show']);

    // Tags
    Route::resource('tags', CandidateTagController::class)->only(['index', 'store']);
    Route::post('candidates/{candidate}/tags', [CandidateTagController::class, 'attachToCandidate'])->name('candidates.tags.attach');
    Route::delete('candidates/{candidate}/tags', [CandidateTagController::class, 'detachFromCandidate'])->name('candidates.tags.detach');

    // Pipeline
    Route::get('pipeline/{job}', [PipelineController::class, 'show'])->name('pipeline.show');
    Route::patch('pipeline/applications/{application}/move', [PipelineController::class, 'move'])->name('pipeline.move');
    Route::patch('pipeline/applications/{application}/reject', [PipelineController::class, 'reject'])->name('pipeline.reject');

    // Pipeline Stages
    Route::resource('pipeline-stages', PipelineStageController::class)->except(['create', 'edit', 'show']);
    Route::post('pipeline-stages/reorder', [PipelineStageController::class, 'reorder'])->name('pipeline-stages.reorder');

    // AI
    Route::prefix('ai')->name('ai.')->middleware('permission:ai.parse_resume')->group(function () {
        Route::post('candidates/{candidate}/parse', [AIController::class, 'parseResume'])->name('parse');
        Route::post('applications/{application}/score', [AIController::class, 'scoreCandidate'])->name('score');
        Route::post('jobs/{job}/bulk-score', [AIController::class, 'bulkScore'])->name('bulk-score');
        Route::post('candidates/{candidate}/summarize', [AIController::class, 'summarize'])->name('summarize');
        Route::post('applications/{application}/questions', [AIController::class, 'generateQuestions'])->name('questions');
    });

    // Interviews
    Route::resource('interviews', InterviewController::class)->except(['create', 'edit']);
    Route::post('interviews/{interview}/scorecard', [ScorecardController::class, 'store'])->name('scorecards.store');

    // Offers
    Route::resource('offers', OfferController::class)->only(['index', 'show', 'store']);
    Route::get('offers/create/{application}', [OfferController::class, 'create'])->name('offers.create');
    Route::post('offers/{offer}/send', [OfferController::class, 'send'])->name('offers.send');
    Route::get('offers/{offer}/pdf', [OfferController::class, 'downloadPdf'])->name('offers.pdf');

    // Offer Templates
    Route::resource('offer-templates', OfferTemplateController::class)->except(['create', 'edit', 'show']);

    // Comments
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Activities
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->middleware('permission:analytics.view')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/time-to-hire', [AnalyticsController::class, 'timeToHire'])->name('time-to-hire');
        Route::get('/pipeline-conversion', [AnalyticsController::class, 'pipelineConversion'])->name('pipeline-conversion');
        Route::get('/sources', [AnalyticsController::class, 'sources'])->name('sources');
        Route::get('/team', [AnalyticsController::class, 'teamPerformance'])->name('team');
    });

    // Billing
    Route::prefix('billing')->name('billing.')->middleware('permission:company.manage_billing')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('/subscribe', [BillingController::class, 'subscribe'])->name('subscribe');
        Route::patch('/plan', [BillingController::class, 'changePlan'])->name('change-plan');
        Route::post('/cancel', [BillingController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [BillingController::class, 'resume'])->name('resume');
        Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
        Route::patch('/payment-method', [BillingController::class, 'updatePaymentMethod'])->name('payment-method');
    });

    // Settings
});

require __DIR__.'/auth.php';
