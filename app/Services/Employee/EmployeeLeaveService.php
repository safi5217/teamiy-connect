<?php

namespace App\Services\Employee;

use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\TimeLeave;
use App\Support\SharedTableId;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Holiday;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeLeaveService
{
    public function storeFullLeave(array $data, $employee): LeaveRequestMaster
    {

        $from = Carbon::parse($data['leave_from'])->startOfDay();
        $to = Carbon::parse($data['leave_to'])->startOfDay();

        $leaveType = LeaveType::where('id', $data['leave_type_id'])
            ->where('branch_id', $employee->branch_id)
            ->where('is_active', 1)
            ->first();

        if (!$leaveType) {
            throw new Exception('Invalid leave type selected.');
        }

        $requestedDays = $this->countWorkingDays($from, $to, $employee);
        if ($requestedDays <= 0) {
            throw new Exception('Selected dates are only weekend/holiday. Please select working days.');
        }

        $existingLeave = LeaveRequestMaster::where('requested_by', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('leave_from', [$from, $to])
                    ->orWhereBetween('leave_to', [$from, $to])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('leave_from', '<=', $from)
                            ->where('leave_to', '>=', $to);
                    });
            })
            ->exists();

        if ($existingLeave) {
            throw new Exception('You already have a leave request in this date range.');
        }

        $usedLeaves = LeaveRequestMaster::where('requested_by', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereYear('leave_from', $from->year)
            ->sum('no_of_days');

        if (($usedLeaves + $requestedDays) > $leaveType->leave_allocated) {
            $left = max($leaveType->leave_allocated - $usedLeaves, 0);
            throw new Exception("You only have {$left} leave days left for {$leaveType->name}.");
        }

        return DB::transaction(function () use ($data, $employee, $from, $to, $requestedDays) {
            return LeaveRequestMaster::create([
                'id' => SharedTableId::next(LeaveRequestMaster::class),
                'title' => $data['title'] ?? 'Leave request',
                'no_of_days' => $requestedDays,
                'leave_type_id' => $data['leave_type_id'],
                'leave_requested_date' => now(),
                'leave_from' => $from,
                'leave_to' => $to,
                'status' => 'pending',
                'reasons' => $data['reasons'],
                'company_id' => $employee->company_id,
                'requested_by' => $employee->id,
                'early_exit' => false,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
            ]);
        });
    }

    public function storeShortLeave(array $data, $employee): TimeLeave
    {
        $issueDate = Carbon::parse($data['issue_date'])->format('Y-m-d');

        $existing = TimeLeave::where('requested_by', $employee->id)
            ->where('issue_date', $issueDate)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            throw new Exception('You already have a short leave request for this date.');
        }

        return DB::transaction(function () use ($data, $employee, $issueDate) {
            return TimeLeave::create([
                'id' => SharedTableId::next(TimeLeave::class),
                'issue_date' => $issueDate,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'pending',
                'reasons' => $data['reasons'],
                'requested_by' => $employee->id,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'company_id' => $employee->company_id,
            ]);
        });
    }
    private function countWorkingDays(Carbon $from, Carbon $to, $employee): int
    {
        $company = Company::find($employee->company_id);

        $weekends = $company?->weekend ?? [];

        if (is_string($weekends)) {
            $weekends = json_decode($weekends, true) ?: [];
        }

        $weekends = array_map('intval', $weekends);

        $holidays = Holiday::where('company_id', $employee->company_id)
            ->whereBetween('event_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->pluck('event_date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $workingDays = 0;

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            $dateString = $date->format('Y-m-d');

            if (in_array($dayOfWeek, $weekends, true)) {
                continue;
            }

            if (in_array($dateString, $holidays, true)) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }
}
