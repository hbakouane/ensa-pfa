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
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('loggable');
            $table->string('action'); // parse_resume, score_candidate, summarize, generate_questions
            $table->string('model'); // gpt-4o
            $table->integer('input_tokens');
            $table->integer('output_tokens');
            $table->integer('total_tokens');
            $table->decimal('cost', 10, 6)->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('status'); // success, failed, rate_limited
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('company_id', 'ai_usage_logs_company_id_index');
            $table->index('user_id', 'ai_usage_logs_user_id_index');
            $table->index('action', 'ai_usage_logs_action_index');
            $table->index('status', 'ai_usage_logs_status_index');
            $table->index('created_at', 'ai_usage_logs_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
