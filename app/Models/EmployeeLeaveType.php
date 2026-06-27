<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveType extends Model
{
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveRequestMaster::class, 'leave_type_id');
    }

    
}