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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('offer_templates')->nullOnDelete();
            $table->decimal('salary', 12, 2);
            $table->string('salary_currency')->default('USD');
            $table->enum('salary_period', ['yearly', 'monthly', 'hourly'])->default('yearly');
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->longText('content');
            $table->string('token', 64)->unique();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'sent', 'accepted', 'declined', 'expired', 'withdrawn'])->default('draft');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('company_id', 'offers_company_id_index');
            $table->index('application_id', 'offers_application_id_index');
            $table->index('template_id', 'offers_template_id_index');
            $table->index('created_by', 'offers_created_by_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
