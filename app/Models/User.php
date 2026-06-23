<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'work_email',
    'username',
    'password',
    'phone',
    'address',
    'avatar',
    'status',
    'is_active',
    'company_id',
    'branch_id',
    'department_id',
    'post_id',
    'supervisor_id',
    'office_time_id',
    'remember_token',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public function scopeActiveEmployee(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('status', 'verified');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'email_verified_at' => 'datetime',
            'joining_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'is_active' => 'boolean',
            'online_status' => 'boolean',
            'logout_status' => 'boolean',
            'allow_holiday_check_in' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
