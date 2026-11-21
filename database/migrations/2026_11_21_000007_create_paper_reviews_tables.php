<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('paper_review_claim_id')->unique()
                ->constrained('paper_review_claims')->restrictOnDelete();
            $table->foreignId('paper_id')->constrained('papers')->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->text('general_comments')->nullable();
            $table->string('recommendation', 12)->nullable();
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['paper_id', 'status']);
            $table->index(['reviewer_id', 'status']);
        });

        Schema::create('paper_review_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('paper_review_id')
                ->constrained('paper_reviews')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('event', 32);
            $table->unsignedInteger('version');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique(['paper_review_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_review_events');
        Schema::dropIfExists('paper_reviews');
    }
};
