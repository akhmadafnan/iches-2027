<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table): void {
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('submission_version')->default(0);
        });

        Schema::create('paper_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paper_id')->constrained('papers')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('event', 48);
            $table->string('from_status', 32);
            $table->string('to_status', 32);
            $table->unsignedInteger('submission_version');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique(['paper_id', 'event', 'submission_version']);
            $table->index(['paper_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_status_histories');

        Schema::table('papers', function (Blueprint $table): void {
            $table->dropColumn(['submitted_at', 'submission_version']);
        });
    }
};
