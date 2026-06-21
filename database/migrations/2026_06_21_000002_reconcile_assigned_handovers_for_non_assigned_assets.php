<?php

use App\Enums\AssetAssignmentStatus;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $returnDate = now(config('app.timezone', 'Asia/Kolkata'))->toDateString();

        AssetAssignment::query()
            ->with('asset:id,status,condition')
            ->where('status', AssetAssignmentStatus::Assigned->value)
            ->whereHas('asset', fn ($query) => $query->where('status', '!=', 'assigned'))
            ->get()
            ->each(function (AssetAssignment $assignment) use ($returnDate): void {
                $asset = $assignment->asset;

                if (! $asset) {
                    return;
                }

                $note = sprintf(
                    'Closed automatically because asset status is %s.',
                    $asset->status->label()
                );

                $assignment->update([
                    'status' => AssetAssignmentStatus::Returned,
                    'returned_at' => $returnDate,
                    'return_condition' => $asset->condition,
                    'return_notes' => trim($assignment->return_notes ? $assignment->return_notes."\n".$note : $note),
                ]);

                AssetReturn::query()->firstOrCreate(
                    ['asset_assignment_id' => $assignment->id],
                    [
                        'asset_id' => $assignment->asset_id,
                        'employee_id' => $assignment->employee_id,
                        'received_by' => null,
                        'returned_at' => $returnDate,
                        'condition' => $asset->condition,
                        'notes' => $note,
                    ]
                );
            });
    }

    public function down(): void
    {
        //
    }
};
