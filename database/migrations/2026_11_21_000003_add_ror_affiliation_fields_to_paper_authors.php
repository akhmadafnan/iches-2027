<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paper_authors', function (Blueprint $table): void {
            // Legacy rows retain their original text; no unverified ID is invented.
            $table->string('ror_id', 48)->nullable()->after('institution');
            $table->string('affiliation_source', 16)->nullable()->after('ror_id');
            $table->index('ror_id');
        });
    }

    public function down(): void
    {
        Schema::table('paper_authors', function (Blueprint $table): void {
            $table->dropIndex(['ror_id']);
            $table->dropColumn(['ror_id', 'affiliation_source']);
        });
    }
};
