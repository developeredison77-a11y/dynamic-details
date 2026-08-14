<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->string('invoice_file_path')->nullable()->after('notes');
            $table->string('invoice_original_name')->nullable()->after('invoice_file_path');
            $table->string('invoice_mime_type')->nullable()->after('invoice_original_name');
            $table->unsignedBigInteger('invoice_file_size')->nullable()->after('invoice_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->dropColumn([
                'invoice_file_path',
                'invoice_original_name',
                'invoice_mime_type',
                'invoice_file_size',
            ]);
        });
    }
};
