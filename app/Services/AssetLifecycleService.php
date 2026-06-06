<?php

namespace App\Services;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use Illuminate\Support\Facades\DB;

class AssetLifecycleService
{
    /**
     * @param array<string, mixed> $data
     */
    public function handover(array $data, ?int $userId): AssetAssignment
    {
        return DB::transaction(function () use ($data, $userId): AssetAssignment {
            $asset = Asset::query()->lockForUpdate()->findOrFail($data['asset_id']);

            if ($asset->status !== AssetStatus::Available && $asset->status !== AssetStatus::Returned) {
                throw new \DomainException('This asset is not available for handover.');
            }

            $assignment = AssetAssignment::query()->create([
                'employee_id' => $data['employee_id'],
                'asset_id' => $asset->id,
                'created_by' => $userId,
                'status' => AssetAssignmentStatus::Assigned,
                'handover_date' => $data['handover_date'],
                'expected_return_date' => $data['expected_return_date'] ?? null,
                'handover_notes' => $data['handover_notes'] ?? null,
            ]);

            $asset->update(['status' => AssetStatus::Assigned]);

            return $assignment->load(['employee', 'asset.brand', 'asset.category']);
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function returnAsset(AssetAssignment $assignment, array $data, ?int $userId): AssetReturn
    {
        return DB::transaction(function () use ($assignment, $data, $userId): AssetReturn {
            $assignment = AssetAssignment::query()->lockForUpdate()->with('asset')->findOrFail($assignment->id);

            if ($assignment->status !== AssetAssignmentStatus::Assigned) {
                throw new \DomainException('This handover has already been returned.');
            }

            $condition = AssetCondition::from($data['condition']);
            $nextStatus = $condition === AssetCondition::Damaged ? AssetStatus::Maintenance : AssetStatus::Available;

            $assignment->update([
                'status' => AssetAssignmentStatus::Returned,
                'returned_at' => $data['returned_at'],
                'return_condition' => $condition,
                'return_notes' => $data['notes'] ?? null,
            ]);

            $assignment->asset->update([
                'status' => $nextStatus,
                'condition' => $condition,
            ]);

            return AssetReturn::query()->create([
                'asset_assignment_id' => $assignment->id,
                'asset_id' => $assignment->asset_id,
                'employee_id' => $assignment->employee_id,
                'received_by' => $userId,
                'returned_at' => $data['returned_at'],
                'condition' => $condition,
                'notes' => $data['notes'] ?? null,
            ])->load(['employee', 'asset']);
        });
    }
}
