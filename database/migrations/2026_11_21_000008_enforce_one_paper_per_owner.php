<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('papers')
            ->select('owner_id')
            ->groupBy('owner_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists()) {
            throw new RuntimeException('Cannot enforce one paper per owner: duplicate owner records must be reviewed manually.');
        }

        Schema::table('papers', function (Blueprint $table): void {
            $table->unique('owner_id', 'papers_owner_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table): void {
            $table->dropUnique('papers_owner_id_unique');
        });
    }
};
