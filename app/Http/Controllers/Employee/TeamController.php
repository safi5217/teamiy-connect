<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Models\User;
use Illuminate\Contracts\View\View;

class TeamController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();

        return view('team.index', [
            'employee' => $employee,
            'members' => User::query()
                ->with(['branch', 'department', 'post', 'supervisor'])
                ->where('company_id', $employee->company_id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderByRaw('department_id = ? desc', [$employee->department_id])
                ->orderBy('name')
                ->paginate(24),
        ]);
    }
}
