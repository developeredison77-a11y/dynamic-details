<?php

namespace App\Models;

use App\Enums\ImportType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['created_by', 'type', 'file_name', 'total_rows', 'successful_rows', 'failed_rows', 'errors'])]
class ImportBatch extends Model
{
    protected function casts(): array
    {
        return [
            'type' => ImportType::class,
            'errors' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
