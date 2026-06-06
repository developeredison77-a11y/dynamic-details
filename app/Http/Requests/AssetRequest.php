<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $assetId = $this->route('asset')?->id;

        return [
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'asset_brand_id' => ['nullable', 'exists:asset_brands,id'],
            'asset_tag' => ['required', 'string', 'max:120', Rule::unique('assets', 'asset_tag')->ignore($assetId)],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')->ignore($assetId)],
            'model' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'condition' => ['required', Rule::enum(AssetCondition::class)],
            'purchased_at' => ['nullable', 'date'],
            'purchase_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
