<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'description',
        'notice_publish_date',
        'company_id',
        'is_active',
        'created_by',
        'updated_by',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'notice_publish_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function receivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notice_receivers', 'notice_id', 'notice_receiver_id')
            ->withPivot('id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
