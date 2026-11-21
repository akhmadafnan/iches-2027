<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table): void {
            $table->longText('abstract_text')->nullable();
            $table->json('keywords')->nullable();
            $table->string('abstract_pdf_path', 512)->nullable();
            $table->string('abstract_pdf_original_name', 255)->nullable();
            $table->unsignedBigInteger('abstract_pdf_size')->nullable();
            $table->timestamp('abstract_pdf_uploaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table): void {
            $table->dropColumn([
                'abstract_text',
                'keywords',
                'abstract_pdf_path',
                'abstract_pdf_original_name',
                'abstract_pdf_size',
                'abstract_pdf_uploaded_at',
            ]);
        });
    }
};
