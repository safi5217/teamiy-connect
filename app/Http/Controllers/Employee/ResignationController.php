<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Http\Requests\Employee\StoreResignationRequest;
use App\Models\Resignation;
use App\Models\Termination;
use App\Models\Transfer;
use App\Support\SharedTableId;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ResignationController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee()->load('supervisor');
        $resignations = Resignation::query()
            ->where('employee_id', $employee->id)
            ->latest('resignation_date')
            ->get();

        return view('resignation.index', [
            'employee' => $employee,
            'latestResignation' => $resignations->first(),
            'resignations' => $resignations,
            'transfers' => Transfer::query()
                ->where('employee_id', $employee->id)
                ->latest('transfer_date')
                ->limit(10)
                ->get(),
            'terminations' => Termination::query()
                ->where('employee_id', $employee->id)
                ->latest('termination_date')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(StoreResignationRequest $request): RedirectResponse
    {
        $employee = $this->employee();
        $validated = $request->validated();
        $documentPath = $request->file('document')?->store('resignation-documents', 'public');

        DB::transaction(function () use ($documentPath, $employee, $validated): void {
            Resignation::query()->create([
                'id' => SharedTableId::next(Resignation::class),
                'employee_id' => $employee->id,
                'resignation_date' => $validated['resignation_date'],
                'last_working_day' => $validated['last_working_day'],
                'reason' => $validated['reason'],
                'document' => $documentPath,
                'status' => 'pending',
                'created_by' => $employee->id,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
            ]);
        });

        return back()->with('status', 'Resignation request submitted.');
    }
}
