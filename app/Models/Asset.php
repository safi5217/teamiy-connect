<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purchased_date' => 'date',
            'warranty_available' => 'boolean',
            'warranty_end_date' => 'date',
            'is_available' => 'boolean',
            'is_repaired' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
