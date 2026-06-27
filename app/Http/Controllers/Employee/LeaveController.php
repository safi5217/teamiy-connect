<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Http\Requests\Employee\StoreLeaveRequest;
use App\Http\Requests\Employee\StoreTimeLeaveRequest;
use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\TimeLeave;
use App\Support\SharedTableId;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;


class LeaveController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $leaveTypes = LeaveType::where('branch_id', $employee->branch_id)->get();
       

        return view('leave.index', [
            'employee' => $employee,
            'leavetype' => $leaveTypes,
            'employeeleaves' => $employee->employeeLeaveTypes()->with('leaveType')->get(),
            'leaveRequests' => $employee->leaveRequests()
                ->with('leaveType')
                ->latest('leave_requested_date')
                ->paginate(15),
            'timeLeaves' => $employee->timeLeaves()
                ->latest('issue_date')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $employee = $this->employee();
        $validated = $request->validated();
        $from = Carbon::parse($validated['leave_from'])->startOfDay();
        $to = Carbon::parse($validated['leave_to'])->startOfDay();

        DB::transaction(function () use ($employee, $from, $to, $validated): void {
            LeaveRequestMaster::query()->create([
                'id' => SharedTableId::next(LeaveRequestMaster::class),
                'title' => $validated['title'] ?? 'Leave request',
                'no_of_days' => $from->diffInDays($to) + 1,
                'leave_type_id' => $validated['leave_type_id'] ?? null,
                'leave_requested_date' => now(),
                'leave_from' => $from,
                'leave_to' => $to,
                'status' => 'pending',
                'reasons' => $validated['reasons'],
                'company_id' => $employee->company_id,
                'requested_by' => $employee->id,
                'early_exit' => false,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
            ]);
        });

        return back()->with('status', 'Leave request submitted.');
    }

    public function storeTimeLeave(StoreTimeLeaveRequest $request): RedirectResponse
    {
        $employee = $this->employee();
        $validated = $request->validated();

        DB::transaction(function () use ($employee, $validated): void {
            TimeLeave::query()->create([
                'id' => SharedTableId::next(TimeLeave::class),
                'issue_date' => $validated['issue_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => 'pending',
                'reasons' => $validated['reasons'],
                'requested_by' => $employee->id,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'company_id' => $employee->company_id,
            ]);
        });

        return back()->with('status', 'Time leave request submitted.');
    }
}
