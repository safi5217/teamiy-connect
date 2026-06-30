<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Throwable;

class TeamMeeting extends Model
{
    protected $fillable = [
        'title',
        'description',
        'venue',
        'meeting_date',
        'meeting_start_time',
        'company_id',
        'image',
        'meeting_published_at',
        'created_by',
        'updated_by',
        'branch_id',
        'meeting_link',
        'admin_link',
        'meeting_password',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'meeting_published_at' => 'datetime',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_meeting_members', 'team_meeting_id', 'meeting_participator_id')
            ->withPivot('id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'meeting_departments', 'team_meeting_id', 'department_id')
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

    public function scheduledAt(): ?CarbonImmutable
    {
        if (! $this->meeting_date || ! $this->meeting_start_time) {
            return null;
        }

        try {
            return CarbonImmutable::parse($this->meeting_date->format('Y-m-d').' '.$this->meeting_start_time);
        } catch (Throwable) {
            return null;
        }
    }

    public function isCompleted(): bool
    {
        return (bool) $this->scheduledAt()?->isPast();
    }

    public function displayStatus(): string
    {
        return $this->isCompleted() ? 'completed' : 'scheduled';
    }
}
