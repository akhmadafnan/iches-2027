<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('conference_track_id')->constrained('conference_tracks')->restrictOnDelete();
            $table->ulid('public_id')->unique();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('title', 255);
            $table->string('status', 32)->default('draft');
            $table->timestamps();

            $table->index(['owner_id', 'status', 'updated_at']);
            $table->index(['conference_track_id', 'status']);
        });

        Schema::create('paper_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paper_id')->constrained('papers')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('name', 150);
            $table->string('email', 254);
            $table->string('institution', 255)->nullable();
            $table->string('country', 120)->nullable();
            $table->boolean('is_corresponding')->default(false);
            $table->timestamps();

            $table->unique(['paper_id', 'position']);
            $table->unique(['paper_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_authors');
        Schema::dropIfExists('papers');
    }
};
