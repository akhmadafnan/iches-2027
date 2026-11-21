<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 24);
            $table->timestamps();
            $table->unique(['user_id', 'role']);
            $table->index('role');
        });

        // Non-destructive migration: every historic primary role becomes its first membership.
        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunkById(200, function ($users): void {
            $now = now();
            $records = [];
            foreach ($users as $user) {
                $records[] = [
                    'user_id' => $user->id,
                    'role' => $user->role ?: 'user',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($records !== []) {
                DB::table('user_roles')->insert($records);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
