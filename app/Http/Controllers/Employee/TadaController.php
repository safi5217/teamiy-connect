<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Http\Requests\Employee\StoreTadaRequest;
use App\Models\Tada;
use App\Support\SharedTableId;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TadaController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $allTadas = Tada::query()
            ->where('employee_id', $employee->id)
            ->get();

        return view('tada.index', [
            'employee' => $employee,
            'tadaStats' => [
                'total_claimed' => $allTadas->sum('total_expense'),
                'approved' => $allTadas
                    ->filter(fn (Tada $tada): bool => in_array(strtolower((string) $tada->status), ['approved', 'accepted'], true))
                    ->sum('total_expense'),
                'pending' => $allTadas
                    ->filter(fn (Tada $tada): bool => strtolower((string) $tada->status) === 'pending')
                    ->sum('total_expense'),
                'paid' => $allTadas
                    ->where('is_settled', true)
                    ->sum('total_expense'),
            ],
            'tadas' => Tada::query()
                ->where('employee_id', $employee->id)
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(StoreTadaRequest $request): RedirectResponse
    {
        $employee = $this->employee();
        $validated = $request->validated();

        DB::transaction(function () use ($employee, $validated): void {
            Tada::query()->create([
                'id' => SharedTableId::next(Tada::class),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'total_expense' => $validated['total_expense'],
                'status' => 'pending',
                'is_active' => true,
                'is_settled' => false,
                'employee_id' => $employee->id,
                'created_by' => $employee->id,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
            ]);
        });

        return back()->with('status', 'TADA claim submitted.');
    }
}
