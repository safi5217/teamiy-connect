<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! function_exists('attendance_find_record')) {
    function attendance_find_record(string $table, $id, string $column = 'id')
    {
        if (empty($id)) {
            return null;
        }

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return null;
        }

        return DB::table($table)
            ->where($column, $id)
            ->first();
    }
}

if (! function_exists('attendance_pick_value')) {
    function attendance_pick_value($record, array $columns, $default = null)
    {
        if (! $record) {
            return $default;
        }

        foreach ($columns as $column) {
            $value = data_get($record, $column);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }
}

if (! function_exists('attendance_apply_latest')) {
    function attendance_apply_latest($query, array $preferredColumns = [])
    {
        try {
            $table = $query->getModel()->getTable();

            foreach ($preferredColumns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    return $query->latest($column);
                }
            }

            if (Schema::hasColumn($table, 'created_at')) {
                return $query->latest('created_at');
            }
        } catch (\Throwable $e) {
            return $query;
        }

        return $query;
    }
}

if (! function_exists('attendance_today_count')) {
    function attendance_today_count(int $userId, string $type): int
    {
        if (! Schema::hasTable('attendances')) {
            return 0;
        }

        $userColumn = null;

        foreach (['user_id', 'employee_id', 'staff_id'] as $column) {
            if (Schema::hasColumn('attendances', $column)) {
                $userColumn = $column;
                break;
            }
        }

        if (! $userColumn) {
            return 0;
        }

        $query = DB::table('attendances')
            ->where($userColumn, $userId);

        if (Schema::hasColumn('attendances', 'attendance_date')) {
            $query->whereDate('attendance_date', today());
        } elseif (Schema::hasColumn('attendances', 'date')) {
            $query->whereDate('date', today());
        } elseif (Schema::hasColumn('attendances', 'created_at')) {
            $query->whereDate('created_at', today());
        }

        if ($type === 'login') {
            if (Schema::hasColumn('attendances', 'type')) {
                return (clone $query)
                    ->whereIn('type', ['login', 'check_in', 'checkin', 'in'])
                    ->count();
            }

            foreach (['check_in_at', 'check_in', 'in_time'] as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    return (clone $query)->whereNotNull($column)->count();
                }
            }
        }

        if ($type === 'logout') {
            if (Schema::hasColumn('attendances', 'type')) {
                return (clone $query)
                    ->whereIn('type', ['logout', 'check_out', 'checkout', 'out'])
                    ->count();
            }

            foreach (['check_out_at', 'check_out', 'out_time'] as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    return (clone $query)->whereNotNull($column)->count();
                }
            }
        }

        return 0;
    }
}

if (! function_exists('employee_auth_user')) {
    function employee_auth_user()
    {
        foreach (['employee', 'web'] as $guard) {
            try {
                if (auth()->guard($guard)->check()) {
                    return auth()->guard($guard)->user();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return auth()->user();
    }
}

if (! function_exists('employee_full_details')) {
    function employee_full_details($employeeId = null): array
    {
        $authEmployee = employee_auth_user();

        if (! $authEmployee && ! $employeeId) {
            return [
                'status' => false,
                'message' => 'Employee not authenticated.',
                'employee' => null,
            ];
        }

        $id = $employeeId ?: $authEmployee->id;

        $employee = User::query()
            ->with([
                'company',
                'branch',
                'department',
                'post',
                'officeTime',
                'supervisor',

                'employeeAccount',
                'employeeSalary',
                'employeeLeaveTypes.leaveType',

                'todayAttendances',
                'attendances' => function ($query) {
                    attendance_apply_latest($query, ['attendance_date', 'date', 'created_at'])->limit(20);
                },

                'assetAssignments.asset',
                'assets',

                'leaveRequests.leaveType',
                'timeLeaves' => function ($query) {
                    attendance_apply_latest($query, ['date', 'leave_date', 'created_at']);
                },

                'tadas' => function ($query) {
                    attendance_apply_latest($query, ['created_at']);
                },
                'advanceSalaries' => function ($query) {
                    attendance_apply_latest($query, ['created_at']);
                },
                'awards' => function ($query) {
                    attendance_apply_latest($query, ['created_at']);
                },
                'payslips' => function ($query) {
                    attendance_apply_latest($query, ['created_at']);
                },
                'teamMeetings' => function ($query) {
                    attendance_apply_latest($query, ['meeting_date', 'created_at']);
                },
                'notices' => function ($query) {
                    attendance_apply_latest($query, ['notice_publish_date', 'created_at']);
                },
            ])
            ->withCount([
                'attendances',
                'leaveRequests',
                'timeLeaves',
                'tadas',
                'advanceSalaries',
                'awards',
                'payslips',
                'assetAssignments',
                'teamMeetings',
                'notices',
            ])
            ->find($id);

        if (! $employee) {
            return [
                'status' => false,
                'message' => 'Employee not found.',
                'employee' => null,
            ];
        }

        return [
            'status' => true,
            'message' => 'Employee details loaded successfully.',
            'employee' => $employee,

            'basic' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'work_email' => $employee->work_email,
                'phone' => $employee->phone,
                'employee_code' => $employee->employee_code,
                'status' => $employee->status,
                'is_active' => $employee->is_active,
                'employment_type' => $employee->employment_type,
                'user_type' => $employee->user_type,
                'joining_date' => $employee->joining_date,
            ],

            'company_details' => [
                'company' => $employee->company,
                'branch' => $employee->branch,
                'department' => $employee->department,
                'post' => $employee->post,
                'supervisor' => $employee->supervisor,
                'office_time' => $employee->officeTime,
            ],

            'salary_details' => [
                'account' => $employee->employeeAccount,
                'salary' => $employee->employeeSalary,
                'payslips' => $employee->payslips,
            ],

            'leave_details' => [
                'leave_types' => $employee->employeeLeaveTypes,
                'leave_requests' => $employee->leaveRequests,
                'time_leaves' => $employee->timeLeaves,
            ],

            'attendance_details' => [
                'today' => $employee->todayAttendances,
                'recent' => $employee->attendances,
            ],

            'asset_details' => [
                'assignments' => $employee->assetAssignments,
                'assets' => $employee->assets,
            ],

            'other_details' => [
                'tadas' => $employee->tadas,
                'advance_salaries' => $employee->advanceSalaries,
                'awards' => $employee->awards,
                'team_meetings' => $employee->teamMeetings,
                'notices' => $employee->notices,
            ],

            'counts' => [
                'attendances' => $employee->attendances_count,
                'leave_requests' => $employee->leave_requests_count,
                'time_leaves' => $employee->time_leaves_count,
                'tadas' => $employee->tadas_count,
                'advance_salaries' => $employee->advance_salaries_count,
                'awards' => $employee->awards_count,
                'payslips' => $employee->payslips_count,
                'assets' => $employee->asset_assignments_count,
                'team_meetings' => $employee->team_meetings_count,
                'notices' => $employee->notices_count,
            ],
        ];
    }
}

if (! function_exists('attendance_is_approved')) {
    function attendance_is_approved($record): bool
    {
        $status = strtolower((string) attendance_pick_value($record, [
            'status',
            'approval_status',
            'leave_status',
            'request_status',
        ], ''));

        if (in_array($status, ['approved', 'approve', 'accepted'], true)) {
            return true;
        }

        if (in_array($status, ['pending', 'rejected', 'declined', 'cancelled', 'canceled'], true)) {
            return false;
        }

        $isApproved = attendance_pick_value($record, [
            'is_approved',
            'approved',
        ], null);

        if ($isApproved !== null) {
            return (int) $isApproved === 1;
        }

        return false;
    }
}

if (! function_exists('attendance_record_covers_today')) {
    function attendance_record_covers_today($record, Carbon $today): bool
    {
        $from = attendance_pick_value($record, [
            'from_date',
            'start_date',
            'leave_from',
            'date_from',
            'leave_date',
            'request_date',
            'date',
            'created_at',
        ]);

        $to = attendance_pick_value($record, [
            'to_date',
            'end_date',
            'leave_to',
            'date_to',
            'leave_date',
            'request_date',
            'date',
            'created_at',
        ], $from);

        if (! $from) {
            return false;
        }

        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to ?: $from)->endOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        return $today->copy()->startOfDay()->betweenIncluded($fromDate, $toDate);
    }
}

if (! function_exists('attendance_is_short_leave_record')) {
    function attendance_is_short_leave_record($record): bool
    {
        $text = strtolower(trim(implode(' ', array_filter([
            attendance_pick_value($record, ['type', 'leave_type', 'request_type', 'category']),
            data_get($record, 'leaveType.name'),
            data_get($record, 'leaveType.title'),
            data_get($record, 'leave_type.name'),
            data_get($record, 'leave_type.title'),
            data_get($record, 'leaveType.leave_type'),
        ]))));

        if (
            str_contains($text, 'short') ||
            str_contains($text, 'time') ||
            str_contains($text, 'hour') ||
            str_contains($text, 'half')
        ) {
            return true;
        }

        $fromTime = attendance_pick_value($record, [
            'from_time',
            'start_time',
            'time_from',
            'leave_from_time',
            'short_leave_from',
        ]);

        $toTime = attendance_pick_value($record, [
            'to_time',
            'end_time',
            'time_to',
            'leave_to_time',
            'short_leave_to',
        ]);

        return ! empty($fromTime) && ! empty($toTime);
    }
}

if (! function_exists('attendance_short_leave_window')) {
    function attendance_short_leave_window($record, Carbon $today): ?array
    {
        $fromTime = attendance_pick_value($record, [
            'from_time',
            'start_time',
            'time_from',
            'leave_from_time',
            'short_leave_from',
        ]);

        $toTime = attendance_pick_value($record, [
            'to_time',
            'end_time',
            'time_to',
            'leave_to_time',
            'short_leave_to',
        ]);

        if (! $fromTime || ! $toTime) {
            return null;
        }

        try {
            $start = Carbon::parse($today->format('Y-m-d') . ' ' . Carbon::parse($fromTime)->format('H:i:s'));
            $end = Carbon::parse($today->format('Y-m-d') . ' ' . Carbon::parse($toTime)->format('H:i:s'));

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'text' => $start->format('h:i A') . ' to ' . $end->format('h:i A'),
        ];
    }
}

if (! function_exists('attendance_windows_overlap')) {
    function attendance_windows_overlap(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $bStart->lessThan($aEnd);
    }
}

if (! function_exists('attendance_value_is_today')) {
    function attendance_value_is_today($value): bool
    {
        if (! $value) {
            return false;
        }

        try {
            return Carbon::parse($value)->isSameDay(today());
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('attendance_get_check_in')) {
    function attendance_get_check_in($attendance)
    {
        return attendance_pick_value($attendance, [
            'check_in_at',
            'check_in',
            'in_time',
        ]);
    }
}

if (! function_exists('attendance_get_check_out')) {
    function attendance_get_check_out($attendance)
    {
        return attendance_pick_value($attendance, [
            'check_out_at',
            'check_out',
            'out_time',
        ]);
    }
}

if (! function_exists('attendance_sessions_for_js')) {
    function attendance_sessions_for_js($todayAttendances)
    {
        return collect($todayAttendances)->map(function ($attendance) {
            $checkIn = attendance_get_check_in($attendance);
            $checkOut = attendance_get_check_out($attendance);

            try {
                $inCarbon = $checkIn ? Carbon::parse($checkIn) : null;
            } catch (\Throwable $e) {
                $inCarbon = null;
            }

            try {
                $outCarbon = $checkOut ? Carbon::parse($checkOut) : null;
            } catch (\Throwable $e) {
                $outCarbon = null;
            }

            $attendanceDate = attendance_pick_value($attendance, [
                'attendance_date',
                'date',
                'created_at',
            ]);

            try {
                $date = $inCarbon
                    ? $inCarbon->format('Y-m-d')
                    : ($attendanceDate ? Carbon::parse($attendanceDate)->format('Y-m-d') : now()->format('Y-m-d'));
            } catch (\Throwable $e) {
                $date = now()->format('Y-m-d');
            }

            return [
                'date' => $date,
                'inTime' => $inCarbon ? $inCarbon->format('h:i:s A') : '—',
                'outTime' => $outCarbon ? $outCarbon->format('h:i:s A') : '',
                'inEpochMs' => $inCarbon ? $inCarbon->timestamp * 1000 : 0,
                'outEpochMs' => $outCarbon ? $outCarbon->timestamp * 1000 : null,
                'durationMs' => ($inCarbon && $outCarbon)
                    ? $outCarbon->diffInMilliseconds($inCarbon)
                    : 0,
            ];
        })->values();
    }
}

if (! function_exists('attendance_rules')) {
    function attendance_rules($employeeId = null, $loadedEmployee = null, ?array $fullDetails = null): array
    {
        $data = $fullDetails ?: employee_full_details($employeeId);

        if (! data_get($data, 'status')) {
            return $data;
        }

        $employee = $loadedEmployee ?: data_get($data, 'employee');

        if (! $employee) {
            return [
                'status' => false,
                'message' => 'Employee not found.',
                'employee' => null,
            ];
        }

        $officeTime = $employee->officeTime;

        $openingTime = $officeTime?->opening_time ?? '09:00:00';
        $closingTime = $officeTime?->closing_time ?? '18:00:00';

        $today = Carbon::today();
        $now = Carbon::now();

        $startAt = Carbon::parse($today->format('Y-m-d') . ' ' . $openingTime);
        $endAt = Carbon::parse($today->format('Y-m-d') . ' ' . $closingTime);

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt->addDay();
        }

        $effectiveStartAt = $startAt->copy();
        $effectiveEndAt = $endAt->copy();

        $maxCheckIn = 3;
        $maxCheckOut = 3;

        $todayAttendances = collect($employee->todayAttendances ?? [])
            ->filter(function ($attendance) {
                $date = attendance_pick_value($attendance, ['attendance_date', 'date', 'created_at']);
                $checkIn = attendance_get_check_in($attendance);

                return attendance_value_is_today($date) || attendance_value_is_today($checkIn);
            })
            ->values();

        $todayCheckInCount = $todayAttendances
            ->filter(fn ($attendance) => ! empty(attendance_get_check_in($attendance)))
            ->count();

        $todayCheckOutCount = $todayAttendances
            ->filter(fn ($attendance) => ! empty(attendance_get_check_out($attendance)))
            ->count();

        $hasOpenAttendance = $todayAttendances
            ->filter(function ($attendance) {
                return ! empty(attendance_get_check_in($attendance))
                    && empty(attendance_get_check_out($attendance));
            })
            ->isNotEmpty();

        $todayFullLeave = null;

        foreach (collect($employee->leaveRequests ?? []) as $leaveRequest) {
            if (! attendance_is_approved($leaveRequest)) {
                continue;
            }

            if (! attendance_record_covers_today($leaveRequest, $today)) {
                continue;
            }

            if (attendance_is_short_leave_record($leaveRequest)) {
                continue;
            }

            $todayFullLeave = $leaveRequest;
            break;
        }

        $shortLeaveWindows = [];

        $shortLeavesFromRequests = collect($employee->leaveRequests ?? [])->filter(function ($leaveRequest) use ($today) {
            return attendance_is_approved($leaveRequest)
                && attendance_record_covers_today($leaveRequest, $today)
                && attendance_is_short_leave_record($leaveRequest);
        });

        $possibleShortLeaves = collect()
            ->merge(collect($employee->timeLeaves ?? []))
            ->merge($shortLeavesFromRequests);

        foreach ($possibleShortLeaves as $shortLeave) {
            if (! attendance_is_approved($shortLeave)) {
                continue;
            }

            if (! attendance_record_covers_today($shortLeave, $today)) {
                continue;
            }

            $window = attendance_short_leave_window($shortLeave, $today);

            if (! $window) {
                continue;
            }

            if (! attendance_windows_overlap($startAt, $endAt, $window['start'], $window['end'])) {
                continue;
            }

            $shortLeaveWindows[] = $window;

            if ($window['start']->lessThanOrEqualTo($effectiveStartAt) && $window['end']->greaterThan($effectiveStartAt)) {
                $effectiveStartAt = $window['end']->copy();
            }

            if ($window['start']->lessThan($effectiveEndAt) && $window['end']->greaterThanOrEqualTo($effectiveEndAt)) {
                $effectiveEndAt = $window['start']->copy();
            }
        }

        $isInsideShortLeave = collect($shortLeaveWindows)->contains(function ($window) use ($now) {
            return $now->betweenIncluded($window['start'], $window['end']);
        });

        $hasValidOfficeWindow = $effectiveStartAt->lessThan($effectiveEndAt);
        $showAttendanceActions = ! $todayFullLeave;

        $isWithinOfficeTime = $showAttendanceActions
            && $hasValidOfficeWindow
            && $now->betweenIncluded($effectiveStartAt, $effectiveEndAt)
            && ! $isInsideShortLeave;

        $canCheckIn = $showAttendanceActions
            && ! $hasOpenAttendance
            && $isWithinOfficeTime
            && $todayCheckInCount < $maxCheckIn;

        $canCheckOut = $showAttendanceActions
            && $hasOpenAttendance
            && $isWithinOfficeTime
            && $todayCheckOutCount < $maxCheckOut;

        if ($todayFullLeave) {
            $checkInMessage = 'You are on approved leave today. Attendance is not required.';
            $checkOutMessage = 'You are on approved leave today. Attendance is not required.';
        } elseif ($isInsideShortLeave) {
            $activeWindow = collect($shortLeaveWindows)->first(function ($window) use ($now) {
                return $now->betweenIncluded($window['start'], $window['end']);
            });

            $shortLeaveText = $activeWindow['text'] ?? 'approved short leave time';
            $checkInMessage = 'You are on short leave from ' . $shortLeaveText . '.';
            $checkOutMessage = 'You are on short leave from ' . $shortLeaveText . '.';
        } elseif (! $hasValidOfficeWindow) {
            $checkInMessage = 'Today office attendance window is fully covered by approved short leave.';
            $checkOutMessage = 'Today office attendance window is fully covered by approved short leave.';
        } elseif (! $isWithinOfficeTime) {
            $checkInMessage = 'Attendance allowed from ' . $effectiveStartAt->format('h:i A') . ' to ' . $effectiveEndAt->format('h:i A') . '.';
            $checkOutMessage = 'Attendance allowed from ' . $effectiveStartAt->format('h:i A') . ' to ' . $effectiveEndAt->format('h:i A') . '.';
        } else {
            $checkInMessage = $canCheckIn
                ? 'You can check in.'
                : ($hasOpenAttendance ? 'You already have a running attendance session.' : 'Today check in limit completed.');

            $checkOutMessage = $canCheckOut
                ? 'You can check out.'
                : (! $hasOpenAttendance ? 'Please check in first.' : 'Today check out limit completed.');
        }

        return [
            'status' => true,
            'employee' => $employee,

            'leave' => [
                'is_full_day_leave' => (bool) $todayFullLeave,
                'full_day_leave' => $todayFullLeave,
                'has_short_leave' => count($shortLeaveWindows) > 0,
                'is_inside_short_leave' => $isInsideShortLeave,
                'short_leave_windows' => collect($shortLeaveWindows)->map(function ($window) {
                    return [
                        'start_time' => $window['start_time'],
                        'end_time' => $window['end_time'],
                        'text' => $window['text'],
                    ];
                })->values(),
            ],

            'office_time' => [
                'record' => $officeTime,
                'original_opening_time' => $openingTime,
                'original_closing_time' => $closingTime,
                'opening_time' => $effectiveStartAt->format('H:i:s'),
                'closing_time' => $effectiveEndAt->format('H:i:s'),
                'start_at' => $effectiveStartAt->format('Y-m-d H:i:s'),
                'end_at' => $effectiveEndAt->format('Y-m-d H:i:s'),
                'current_time' => $now->format('Y-m-d H:i:s'),
                'is_within_office_time' => $isWithinOfficeTime,
                'has_valid_office_window' => $hasValidOfficeWindow,
            ],

            'limits' => [
                'max_check_in' => $maxCheckIn,
                'max_check_out' => $maxCheckOut,
            ],

            'today_counts' => [
                'check_in' => $todayCheckInCount,
                'check_out' => $todayCheckOutCount,
            ],

            'attendance' => [
                'has_open_attendance' => $hasOpenAttendance,
                'sessions' => attendance_sessions_for_js($todayAttendances),
            ],

            'permissions' => [
                'show_attendance_actions' => $showAttendanceActions,
                'can_check_in' => $canCheckIn,
                'can_check_out' => $canCheckOut,
            ],

            'messages' => [
                'check_in' => $checkInMessage,
                'check_out' => $checkOutMessage,
            ],

            'full_details' => $data,
        ];
    }
}
