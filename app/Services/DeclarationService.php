<?php

namespace App\Services;

use App\Models\AssetAssignment;
use App\Models\AssetDeclaration;

class DeclarationService
{
    public function generate(AssetAssignment $assignment): AssetDeclaration
    {
        return AssetDeclaration::query()->firstOrCreate(
            ['asset_assignment_id' => $assignment->id],
            [
                'declaration_number' => 'DEC-'.now(config('app.timezone', 'Asia/Kolkata'))->format('Ymd').'-'.str_pad((string) ($assignment->id), 5, '0', STR_PAD_LEFT),
                'issued_at' => now(config('app.timezone', 'Asia/Kolkata'))->toDateString(),
                'terms' => 'I confirm receipt of the listed company asset and accept responsibility for its care, safe use, and return when requested.',
            ]
        );
    }
}
