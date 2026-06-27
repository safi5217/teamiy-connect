<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Models\GeneratedPayroll;
use Illuminate\Contracts\View\View;

class PayrollController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $allPayrolls = GeneratedPayroll::query()
            ->where('employee_id', $employee->id)
            ->get();

        return view('payroll.index', [
            'employee' => $employee->load(['employeeAccount', 'employeeSalary', 'payslips']),
            'payrollStats' => [
                'records' => $allPayrolls->count(),
                'base_salary' => $allPayrolls->sum('base_salary'),
                'net_salary' => $allPayrolls->sum('net_salary'),
                'overtime_pay' => $allPayrolls->sum('overtime_pay'),
                'deductions' => $allPayrolls->sum('undertime_deduction') + $allPayrolls->sum('unpaid_leave_deduction') + $allPayrolls->sum('tax'),
                'tada_amount' => $allPayrolls->sum('tada_amount'),
            ],
            'payrolls' => GeneratedPayroll::query()
                ->where('employee_id', $employee->id)
                ->latest()
                ->paginate(15),
        ]);
    }
}
