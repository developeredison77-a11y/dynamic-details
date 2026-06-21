<?php

use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $wrongDate = '2026-06-20';
        $indianDate = '2026-06-21';

        AssetAssignment::query()
            ->where('returned_at', $wrongDate)
            ->where(function ($query): void {
                $query->where('return_notes', 'like', 'Closed automatically%')
                    ->orWhere('return_notes', 'like', "%\nClosed automatically%");
            })
            ->update(['returned_at' => $indianDate]);

        AssetReturn::query()
            ->where('returned_at', $wrongDate)
            ->where('notes', 'like', 'Closed automatically%')
            ->update(['returned_at' => $indianDate]);
    }

    public function down(): void
    {
        //
    }
};
