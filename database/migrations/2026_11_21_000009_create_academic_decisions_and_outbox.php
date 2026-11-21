<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('paper_id')->unique()->constrained('papers')->restrictOnDelete();
            $table->foreignUuid('source_review_id')->constrained('paper_reviews')->restrictOnDelete();
            $table->string('outcome', 16);
            $table->text('public_comments')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('published_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('published_at');
            $table->timestamps();
        });

        Schema::create('paper_decision_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('paper_decision_id')->constrained('paper_decisions')->restrictOnDelete();
            $table->foreignId('paper_id')->constrained('papers')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('event_type', 24);
            $table->string('from_outcome', 16)->nullable();
            $table->string('to_outcome', 16);
            $table->string('reviewer_recommendation', 12);
            $table->text('public_comments')->nullable();
            $table->text('internal_reason')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique(['paper_decision_id', 'version']);
            $table->index(['paper_id', 'occurred_at']);
        });

        Schema::create('decision_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('paper_id')->constrained('papers')->restrictOnDelete();
            $table->foreignUuid('paper_decision_event_id')->unique()->constrained('paper_decision_events')->restrictOnDelete();
            $table->unsignedInteger('decision_version');
            $table->string('event_type', 24);
            $table->string('recipient_email');
            $table->string('recipient_name');
            $table->string('paper_reference');
            $table->string('paper_title');
            $table->string('outcome', 16);
            $table->text('public_comments')->nullable();
            $table->string('status', 16)->default('queued');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['paper_id', 'event_type', 'decision_version'], 'decision_notifications_event_unique');
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_notifications');
        Schema::dropIfExists('paper_decision_events');
        Schema::dropIfExists('paper_decisions');
    }
};
