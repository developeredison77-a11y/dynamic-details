<?php

use Database\Seeders\AccessControlSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => AccessControlSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        //
    }
};
