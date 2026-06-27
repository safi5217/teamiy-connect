<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeId = auth()->id();

        $employee = User::query()
            ->with([
                'company',
                'officeTime',

                'todayAttendances',

                'leaveRequests.leaveType',
                'timeLeaves',
                'employeeLeaveTypes.leaveType',

                'assetAssignments.asset',

                'teamMeetings' => fn($q) => $q->latest('meeting_date'),
                'notices' => fn($q) => $q->latest('notice_publish_date'),
            ])
            ->findOrFail($employeeId);

        $todayAttendances = collect($employee->todayAttendances ?? []);


        $fullDetails = [
            'status' => true,
            'message' => 'Dashboard details loaded successfully.',
            'employee' => $employee,

            'company_details' => [
                'company' => $employee->company,
                'office_time' => $employee->officeTime,
            ],

            'leave_details' => [
                'leave_types' => $employee->employeeLeaveTypes,
                'leave_requests' => $employee->leaveRequests,
                'time_leaves' => $employee->timeLeaves,
            ],

            'attendance_details' => [
                'today' => $todayAttendances,
            ],
        ];

        $attendanceRules = function_exists('attendance_rules')
            ? attendance_rules($employee->id, $employee, $fullDetails)
            : [];


        $userName = $employee->name ?? 'Employee';
        $firstName = explode(' ', $userName)[0] ?? 'Employee';

        $company = $employee->company;
        $officeTime = $employee->officeTime;


        $officeOpening = data_get(
            $attendanceRules,
            'office_time.opening_time',
            $officeTime->opening_time ?? '09:00:00'
        );

        $officeClosing = data_get(
            $attendanceRules,
            'office_time.closing_time',
            $officeTime->closing_time ?? '18:00:00'
        );

        $officeOpeningInput = Carbon::parse($officeOpening)->format('H:i');
        $officeClosingInput = Carbon::parse($officeClosing)->format('H:i');

        $isWithinOfficeTime = data_get(
            $attendanceRules,
            'office_time.is_within_office_time',
            false
        );


        $canCheckIn = data_get($attendanceRules, 'permissions.can_check_in', false);
        $canCheckOut = data_get($attendanceRules, 'permissions.can_check_out', false);
        $showAttendanceActions = data_get($attendanceRules, 'permissions.show_attendance_actions', true);

        $todayCheckInCount = data_get($attendanceRules, 'today_counts.check_in', 0);
        $todayCheckOutCount = data_get($attendanceRules, 'today_counts.check_out', 0);

        $hasOpenAttendance = data_get($attendanceRules, 'attendance.has_open_attendance', false);

        $attendanceActionRoute = $hasOpenAttendance
            ? route('attendance.check-out')
            : route('attendance.check-in');

        $attendanceActionLabel = $hasOpenAttendance
            ? 'Check Out'
            : 'Check In';

        $canSubmitAttendance = $hasOpenAttendance
            ? $canCheckOut
            : $canCheckIn;

        $attendanceDisabledReason = $hasOpenAttendance
            ? data_get($attendanceRules, 'messages.check_out')
            : data_get($attendanceRules, 'messages.check_in');

        $maxCheckIn = data_get($attendanceRules, 'limits.max_check_in', 3);
        $maxCheckOut = data_get($attendanceRules, 'limits.max_check_out', 3);


        $todayWorkedMs = $todayAttendances->sum(function ($attendance) {
            return $this->attendanceDurationMs($attendance);
        });

        $todayWorkedText = $this->millisecondsToText($todayWorkedMs);

        $jsTodaySessions = $todayAttendances
            ->map(fn($attendance) => $this->attendanceSessionForJs($attendance))
            ->values();


        $leaveRequests = collect($employee->leaveRequests ?? []);
        $leaveTypes = collect($employee->employeeLeaveTypes ?? []);

        $leaveBalance = [
            'total' => $employee->leave_allocated ?? 0,
            'pending' => $leaveRequests->where('status', 'pending')->count(),
            'approved' => $leaveRequests->where('status', 'approved')->count(),
            'rejected' => $leaveRequests->where('status', 'rejected')->count(),
            'types' => $leaveTypes->count(),
        ];

        $assetAssignments = collect($employee->assetAssignments ?? []);

        $assetStats = [
            'total' => $assetAssignments->count(),
            'assigned' => $assetAssignments->where('status', 'assigned')->count(),
            'returned' => $assetAssignments->where('status', 'returned')->count(),
            'names' => $assetAssignments
                ->pluck('asset.name')
                ->filter()
                ->take(3)
                ->implode(' · ') ?: 'No assets assigned',
        ];


        $teamMeetings = collect($employee->teamMeetings ?? []);
        $nextMeetingModel = $teamMeetings->first();

        $nextMeeting = [
            'title' => $nextMeetingModel->title
                ?? $nextMeetingModel->meeting_title
                ?? 'No upcoming meeting',

            'time' => $nextMeetingModel
                ? trim(($nextMeetingModel->meeting_date ?? '') . ' · ' . ($nextMeetingModel->meeting_time ?? ''))
                : 'No meeting found',

            'link' => $nextMeetingModel->meeting_link ?? null,
        ];


        $realNotices = collect($employee->notices ?? []);

        $notices = $realNotices->map(function ($notice) {
            $date = $notice->notice_publish_date ?? $notice->created_at ?? null;

            try {
                $date = $date ? Carbon::parse($date)->format('d M Y') : '';
            } catch (\Throwable $e) {
                $date = '';
            }

            return [
                'title' => $notice->title ?? $notice->notice_title ?? 'Notice',
                'category' => $notice->category ?? $notice->notice_type ?? 'General',
                'date' => $date,
                'priority' => $notice->priority ?? 'Normal',
                'read' => $notice->pivot->is_read ?? false,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Holiday placeholder
        |--------------------------------------------------------------------------
        */
        $upcomingHoliday = [
            'title' => 'No upcoming holiday',
            'date' => 'Holiday data not found',
            'remaining' => 'Please connect holidays relation/helper.',
        ];

        return view('dashboard', compact(
            'employee',
            'attendanceRules',

            'userName',
            'firstName',
            'company',
            'officeTime',

            'officeOpening',
            'officeClosing',
            'officeOpeningInput',
            'officeClosingInput',
            'isWithinOfficeTime',

            'canCheckIn',
            'canCheckOut',
            'showAttendanceActions',
            'canSubmitAttendance',
            'attendanceDisabledReason',

            'todayCheckInCount',
            'todayCheckOutCount',
            'hasOpenAttendance',
            'attendanceActionRoute',
            'attendanceActionLabel',

            'maxCheckIn',
            'maxCheckOut',

            'todayAttendances',
            'todayWorkedMs',
            'todayWorkedText',
            'jsTodaySessions',

            'leaveBalance',
            'assetStats',
            'nextMeeting',
            'notices',
            'upcomingHoliday'
        ));
    }

    private function attendanceSessionForJs($attendance): array
    {
        $attendanceDate = $this->attendanceDateString($attendance);

        $checkIn = $attendance->check_in_at
            ? Carbon::parse($attendanceDate . ' ' . $attendance->check_in_at)
            : null;

        $checkOut = $attendance->check_out_at
            ? Carbon::parse($attendanceDate . ' ' . $attendance->check_out_at)
            : null;

        return [
            'date' => $attendanceDate,
            'inTime' => $checkIn?->format('h:i:s A') ?? '-',
            'outTime' => $checkOut?->format('h:i:s A') ?? '',
            'inEpochMs' => $checkIn ? $checkIn->timestamp * 1000 : 0,
            'outEpochMs' => $checkOut ? $checkOut->timestamp * 1000 : null,
            'durationMs' => ($checkIn && $checkOut)
                ? $this->attendanceDurationMs($attendance)
                : 0,
        ];
    }

    private function attendanceDurationMs($attendance): int
    {

        if ($attendance->worked_hour !== null && is_numeric($attendance->worked_hour)) {
            return (int) round(((float) $attendance->worked_hour) * 1000);
        }


        if (! $attendance->check_in_at || ! $attendance->check_out_at) {
            return 0;
        }

        $attendanceDate = $this->attendanceDateString($attendance);

        $checkIn = Carbon::parse($attendanceDate . ' ' . $attendance->check_in_at);
        $checkOut = Carbon::parse($attendanceDate . ' ' . $attendance->check_out_at);

        return abs($checkOut->diffInMilliseconds($checkIn, false));
    }

    private function millisecondsToText(int|float $totalMs): string
    {
        $totalMs = max(0, (int) round($totalMs));

        $hours = floor($totalMs / 3600000);
        $totalMs = $totalMs % 3600000;

        $minutes = floor($totalMs / 60000);
        $totalMs = $totalMs % 60000;

        $seconds = floor($totalMs / 1000);
        $milliseconds = $totalMs % 1000;

        return $hours . 'h '
            . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'm '
            . str_pad($seconds, 2, '0', STR_PAD_LEFT) . 's '
            . str_pad($milliseconds, 3, '0', STR_PAD_LEFT) . 'ms';
    }

    private function attendanceDateString($attendance): string
    {
        if ($attendance->attendance_date instanceof Carbon) {
            return $attendance->attendance_date->toDateString();
        }

        if ($attendance->attendance_date) {
            return Carbon::parse($attendance->attendance_date)->toDateString();
        }

        return today()->toDateString();
    }
}
