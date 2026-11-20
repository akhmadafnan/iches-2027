<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 240);
            $table->string('short_name', 80);
            $table->text('theme')->nullable();
            $table->string('organizer', 240)->nullable();
            $table->string('main_venue', 255)->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->time('start_time')->nullable();
            $table->string('timezone', 80)->default('Asia/Jakarta');
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 48)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('venue', 255)->nullable();
            $table->string('delivery_mode', 24)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['sort_order', 'id']);
        });

        Schema::create('event_milestones', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 48)->unique();
            $table->string('name', 180);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('notes', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_milestones');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('event_settings');
    }
};
