<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_review_claims', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('paper_id')->constrained('papers')->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();

            // MySQL/SQLite permit multiple NULLs under a UNIQUE constraint.
            // An active claim always has active_paper_id = paper_id; released
            // claims set it to NULL but retain their audit record.
            $table->foreignId('active_paper_id')->nullable()->unique()
                ->constrained('papers')->restrictOnDelete();

            $table->timestamp('claimed_at');
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('release_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['reviewer_id', 'active_paper_id']);
            $table->index(['paper_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_review_claims');
    }
};
