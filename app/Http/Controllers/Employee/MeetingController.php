<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Models\TeamMeeting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class MeetingController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $meetingsQuery = TeamMeeting::query()
            ->with([
                'branch',
                'company',
                'creator:id,name,email',
                'departments:id,dept_name,branch_id',
                'members:id,name,email,work_email,avatar,department_id,branch_id',
            ])
            ->where('company_id', $employee->company_id)
            ->where(function (Builder $query) use ($employee): void {
                $query->whereHas('members', fn (Builder $memberQuery) => $memberQuery->whereKey($employee->id));

                if ($employee->department_id) {
                    $query->orWhereHas('departments', fn (Builder $departmentQuery) => $departmentQuery->whereKey($employee->department_id));
                }
            });

        $allMeetings = (clone $meetingsQuery)
            ->orderByDesc('meeting_date')
            ->orderByDesc('meeting_start_time')
            ->get();

        $meetingStats = [
            'total' => $allMeetings->count(),
            'upcoming' => $allMeetings->reject(fn (TeamMeeting $meeting): bool => $meeting->isCompleted())->count(),
            'completed' => $allMeetings->filter(fn (TeamMeeting $meeting): bool => $meeting->isCompleted())->count(),
            'departments' => $allMeetings->pluck('departments')->flatten()->unique('id')->count(),
        ];

        return view('meetings.index', [
            'employee' => $employee,
            'meetingStats' => $meetingStats,
            'meetings' => $meetingsQuery
                ->orderByDesc('meeting_date')
                ->orderByDesc('meeting_start_time')
                ->paginate(15),
        ]);
    }
}
