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
use App\Enums\LeaveGenderEnum;
use Illuminate\Support\Facades\Log;
use App\Services\Employee\EmployeeLeaveService;
use Exception;

class LeaveController extends Controller
{
    use WorksWithEmployee;

    public function __construct(
        protected EmployeeLeaveService $employeeLeaveService
    ) {}
    public function index(): View
    {
        $employee = $this->employee();
        $gender = $employee->gender;
        $leaveTypes = LeaveType::where('branch_id', $employee->branch_id)
            ->where('is_active', 1)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', LeaveGenderEnum::All->value);
            })

            ->orderBy('id')
            ->get()
            ->map(function ($leaveType) use ($employee) {

                $usedLeaves = $employee->leaveRequests()
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'approved')
                    ->sum('no_of_days');

                $leaveType->used_leaves = $usedLeaves;
                $leaveType->left_leaves = $leaveType->leave_allocated - $usedLeaves;

                $leaveType->used_percentage = $leaveType->leave_allocated > 0
                    ? ($usedLeaves / $leaveType->leave_allocated) * 100
                    : 0;

                return $leaveType;
            });

        $leaveTypeIds = $leaveTypes->pluck('id');

        $fullLeaves = LeaveRequestMaster::where('requested_by', $employee->id)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->with('leaveType')
            ->get()
            ->map(function ($leave) {
                $leave->record_type = 'full';
                $leave->sort_date = $leave->leave_requested_date;
                return $leave;
            });

        $shortLeaves = $employee->timeLeaves()
            ->get()
            ->map(function ($leave) {
                $leave->record_type = 'short';
                $leave->sort_date = $leave->issue_date;
                return $leave;
            });

        $leavehistory = $fullLeaves
            ->concat($shortLeaves)
            ->sortByDesc('sort_date')
            ->values();



        return view('leave.index', [
            'employee' => $employee,
            'leavetype' => $leaveTypes,
            'leavehistory' => $leavehistory,
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
        try {
            $this->employeeLeaveService->storeFullLeave(
                $request->validated(),
                $this->employee()
            );

            return back()->with('status', 'Leave request submitted.');
        } catch (Exception $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function storeTimeLeave(StoreTimeLeaveRequest $request): RedirectResponse
    {
        try {
            $this->employeeLeaveService->storeShortLeave(
                $request->validated(),
                $this->employee()
            );

            return back()->with('status', 'Time leave request submitted.');
        } catch (Exception $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }
}
