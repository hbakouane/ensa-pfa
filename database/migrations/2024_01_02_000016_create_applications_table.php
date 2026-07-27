<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->integer('position_in_stage')->default(0);
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->integer('ai_score')->nullable();
            $table->json('ai_score_breakdown')->nullable();
            $table->text('ai_summary')->nullable();
            $table->foreignId('rejection_reason_id')->nullable()->constrained('rejection_reasons')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->nullable();
            $table->timestamp('applied_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['job_id', 'candidate_id'], 'applications_job_id_candidate_id_unique');
            $table->index('company_id', 'applications_company_id_index');
            $table->index('job_id', 'applications_job_id_index');
            $table->index('candidate_id', 'applications_candidate_id_index');
            $table->index('pipeline_stage_id', 'applications_pipeline_stage_id_index');
            $table->index('rejection_reason_id', 'applications_rejection_reason_id_index');
            $table->index('rejected_by', 'applications_rejected_by_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
