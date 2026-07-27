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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->longText('requirements')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->longText('benefits')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'freelance']);
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'lead', 'executive']);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->boolean('show_salary')->default(false);
            $table->enum('remote_policy', ['onsite', 'hybrid', 'remote'])->default('onsite');
            $table->enum('status', ['draft', 'published', 'closed', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('positions_count')->default(1);
            $table->json('application_form_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id', 'job_postings_company_id_index');
            $table->index('department_id', 'job_postings_department_id_index');
            $table->index('location_id', 'job_postings_location_id_index');
            $table->index('category_id', 'job_postings_category_id_index');
            $table->index('status', 'job_postings_status_index');
            $table->index('created_by', 'job_postings_created_by_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
