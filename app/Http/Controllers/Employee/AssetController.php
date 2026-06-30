<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Models\AssetAssignment;
use App\Models\EmployeeDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $assignmentsQuery = AssetAssignment::query()
            ->with([
                'asset.branch',
                'asset.type.branch',
                'branch',
                'department',
            ])
            ->where('user_id', $employee->id);

        $allAssignments = (clone $assignmentsQuery)->get();
        $currentAssignments = $allAssignments->reject(fn (AssetAssignment $assignment): bool => strtolower((string) $assignment->status) === 'returned');
        $returnedAssignments = $allAssignments->filter(fn (AssetAssignment $assignment): bool => strtolower((string) $assignment->status) === 'returned');
        $returnPendingAssignments = $allAssignments->filter(function (AssetAssignment $assignment): bool {
            $status = strtolower(str_replace('_', ' ', (string) $assignment->status));

            return str_contains($status, 'return') && str_contains($status, 'pending');
        });

        $assetStats = [
            'records' => $allAssignments->count(),
            'currently_assigned' => $currentAssignments->count(),
            'returned' => $returnedAssignments->count(),
            'return_pending' => $returnPendingAssignments->count(),
            'working' => $allAssignments->filter(fn (AssetAssignment $assignment): bool => strtolower((string) $assignment->asset?->is_working) === 'yes')->count(),
            'under_warranty' => $allAssignments->filter(fn (AssetAssignment $assignment): bool => (bool) $assignment->asset?->warranty_available
                && $assignment->asset?->warranty_end_date
                && $assignment->asset->warranty_end_date->greaterThanOrEqualTo(now()->startOfDay()))->count(),
            'repaired' => $allAssignments->filter(fn (AssetAssignment $assignment): bool => (bool) $assignment->asset?->is_repaired)->count(),
        ];

        return view('assets.index', [
            'employee' => $employee,
            'assetStats' => $assetStats,
            'assignments' => $assignmentsQuery
                ->latest('assigned_date')
                ->paginate(15),
            'documents' => EmployeeDocument::query()
                ->where('employee_id', $employee->id)
                ->latest()
                ->get(),
        ]);
    }

    public function requestReturn(Request $request, AssetAssignment $assetAssignment): RedirectResponse
    {
        $employee = $this->employee();

        abort_unless((int) $assetAssignment->user_id === (int) $employee->id, 404);

        $status = strtolower((string) $assetAssignment->status);

        if ($status === 'returned') {
            return back()->with('status', 'This asset has already been returned.');
        }

        $normalizedStatus = str_replace('_', ' ', $status);

        if (str_contains($normalizedStatus, 'return') && str_contains($normalizedStatus, 'pending')) {
            return back()->with('status', 'Your return request is already waiting for admin approval.');
        }

        $validated = $request->validate([
            'return_condition' => ['required', Rule::in(['working', 'non_working', 'maintenance'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $assetAssignment->forceFill([
            'status' => 'return_pending',
            'return_condition' => $validated['return_condition'],
            'notes' => $validated['notes'] ?? null,
            'returned_date' => null,
        ])->save();

        return back()->with('status', 'Asset return request sent for admin approval.');
    }
}
