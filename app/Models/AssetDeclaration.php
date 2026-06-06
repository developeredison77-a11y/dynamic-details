<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['asset_assignment_id', 'declaration_number', 'issued_at', 'terms'])]
class AssetDeclaration extends Model
{
    protected function casts(): array
    {
        return ['issued_at' => 'date'];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }
}
