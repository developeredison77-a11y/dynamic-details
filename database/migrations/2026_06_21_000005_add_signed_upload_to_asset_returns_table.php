<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_returns', function (Blueprint $table): void {
            $table->string('signed_file_path')->nullable()->after('notes');
            $table->string('signed_file_name')->nullable()->after('signed_file_path');
            $table->timestamp('signed_uploaded_at')->nullable()->after('signed_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('asset_returns', function (Blueprint $table): void {
            $table->dropColumn(['signed_file_path', 'signed_file_name', 'signed_uploaded_at']);
        });
    }
};
