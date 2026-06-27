@extends('layouts.app')

@section('title', 'Dashboard · Teamiy Connect')
@section('page', 'dashboard')
@section('page_title', 'Dashboard')

@section('content')
    {{-- All dashboard variables are loaded from DashboardController@index. --}}

    <div class="wrap">

        {{-- HERO --}}
        <div class="hero">
            <div class="blob"></div>

            <div class="z">
                <div class="date">{{ now()->format('l, d F Y') }}</div>

                <div class="greet">
                    Good morning, {{ $firstName }} 👋
                </div>

                <div class="summary" id="attSummary">
                    {{ data_get($attendanceRules, 'messages.check_in', "You haven't checked in yet — have a great day!") }}
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <span class="badge xs">
                        {{ $employee->employee_code ?? 'No employee code' }}
                    </span>

                    <span class="badge xs">
                        {{ ucfirst($employee->employment_type ?? 'Employee') }}
                    </span>

                    <span class="badge xs">
                        {{ $company->name ?? 'No company' }}
                    </span>
                </div>
            </div>

            <div class="hero-actions">
                @if ($showAttendanceActions)
                    <form method="POST" action="{{ $attendanceActionRoute }}" data-attendance-form>
                        @csrf

                        <button class="hero-btn" id="heroAttBtn" type="submit" data-db-attendance
                            @disabled(!$canSubmitAttendance) title="{{ $attendanceDisabledReason }}">
                            {{ $attendanceActionLabel }}
                        </button>
                    </form>
                @else
                    <span class="hero-btn ghost" style="cursor:not-allowed"
                        title="{{ data_get($attendanceRules, 'messages.check_in') }}">
                        On Leave Today
                    </span>
                @endif

                <a class="hero-btn ghost" href="{{ url('/leave?new=1') }}">
                    Request leave
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="card card-pad" style="margin-top:18px;color:#0F766E;background:#ECFDF5;border-color:#A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        {{-- TOP CARDS --}}
        <div class="cards-grid auto-250" style="margin-top:18px">

            {{-- ATTENDANCE CARD --}}
            <div class="card card-pad">
                <div class="spread">
                    <span class="lbl">Today's Attendance</span>

                    <span class="ico tint-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 7v5l3 2"></path>
                        </svg>
                    </span>
                </div>

                <div style="margin-top:14px;display:flex;align-items:baseline;gap:8px">
                    <span style="font-size:22px;font-weight:800" class="tc-num" id="attCardValue">
                        0h 00m 00s 000ms
                    </span>

                    <span class="badge xs" id="attStatusBadge">
                        Not checked in
                    </span>
                </div>

                <div style="font-size:12.5px;color:#94A3B8;margin-top:8px" id="attCardSub">
                    {{ data_get($attendanceRules, 'messages.check_in', 'Tap check in to start your day') }}
                </div>

                @if ($showAttendanceActions)
                    <form method="POST" action="{{ $attendanceActionRoute }}" style="margin-top:14px"
                        data-attendance-form>
                        @csrf

                        <button class="btn {{ $hasOpenAttendance ? 'btn-ghost' : 'btn-primary' }} btn-block"
                            id="cardAttBtn" type="submit" data-db-attendance @disabled(!$canSubmitAttendance)
                            title="{{ $attendanceDisabledReason }}">
                            {{ $attendanceActionLabel }}
                        </button>
                    </form>
                @else
                    <div style="margin-top:14px" class="btn btn-ghost btn-block">
                        You are on leave today
                    </div>
                @endif
            </div>

            {{-- SESSION COUNT CARD --}}
            <div class="card card-pad">
                <div class="spread">
                    <span class="lbl">Today Sessions</span>

                    <span class="ico tint-violet">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4.5" width="18" height="16" rx="2.5"></rect>
                            <path d="M3 9h18M8 2.5v4M16 2.5v4"></path>
                        </svg>
                    </span>
                </div>

                <div style="margin-top:14px;display:flex;align-items:baseline;gap:6px">
                    <span style="font-size:28px;font-weight:800" class="tc-num" id="attSessionCount">
                        {{ $todayAttendances->count() }}
                    </span>

                    <span style="font-size:13px;color:#94A3B8;font-weight:600">
                        sessions
                    </span>
                </div>

                <div style="font-size:12.5px;color:#94A3B8;margin-top:8px" id="attSessionSub">
                    Check in {{ $todayCheckInCount }}/{{ $maxCheckIn }} · Check out
                    {{ $todayCheckOutCount }}/{{ $maxCheckOut }}
                </div>
            </div>

            {{-- ATTENDANCE RULES CARD --}}
            <div class="card card-pad">
                <div class="spread">
                    <span class="lbl">Attendance Rules</span>

                    <span class="ico tint-orange">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 7v5l3 2"></path>
                        </svg>
                    </span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px">
                    <label style="font-size:12px;color:#64748B;font-weight:700">
                        From
                        <input type="time" readonly id="attStartTime" value="{{ $officeOpeningInput }}"
                            style="width:100%;margin-top:6px;border:1px solid #E2E8F0;border-radius:10px;padding:9px 10px">
                    </label>

                    <label style="font-size:12px;color:#64748B;font-weight:700">
                        To
                        <input type="time" readonly id="attEndTime" value="{{ $officeClosingInput }}"
                            style="width:100%;margin-top:6px;border:1px solid #E2E8F0;border-radius:10px;padding:9px 10px">
                    </label>
                </div>

                <label style="font-size:12px;color:#64748B;font-weight:700;display:block;margin-top:10px">
                    Max check-in / check-out per day
                    <input type="number" id="attMaxSessions" value="{{ $maxCheckIn }}" min="1"
                        max="10"
                        style="width:100%;margin-top:6px;border:1px solid #E2E8F0;border-radius:10px;padding:9px 10px"
                        readonly>
                </label>

                <div style="font-size:12.5px;color:#94A3B8;margin-top:10px" id="attRuleStatus">
                    Allowed from {{ \Carbon\Carbon::parse($officeOpening)->format('h:i A') }}
                    to {{ \Carbon\Carbon::parse($officeClosing)->format('h:i A') }}
                    · {{ $maxCheckIn }} check-ins and {{ $maxCheckOut }} check-outs allowed.
                </div>
            </div>

            {{-- LEAVE CARD --}}
            <div class="card card-pad clickable" onclick="window.location.href='{{ url('/leave') }}'">
                <div class="spread">
                    <span class="lbl">Leave Summary</span>

                    <span class="ico tint-violet">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2v4M16 2v4M3 10h18"></path>
                            <path d="M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"></path>
                        </svg>
                    </span>
                </div>

                <div style="margin-top:14px;display:flex;align-items:baseline;gap:6px">
                    <span style="font-size:28px;font-weight:800" class="tc-num">
                        {{ $leaveBalance['total'] }}
                    </span>

                    <span style="font-size:13px;color:#94A3B8;font-weight:600">
                        days allocated
                    </span>
                </div>

                <div style="font-size:12.5px;color:#94A3B8;margin-top:8px">
                    <strong style="color:#B26A00">{{ $leaveBalance['pending'] }} pending</strong>
                    · Approved {{ $leaveBalance['approved'] }}
                    · Rejected {{ $leaveBalance['rejected'] }}
                    · Types {{ $leaveBalance['types'] }}
                </div>
            </div>

            {{-- ASSETS CARD --}}
            <div class="card card-pad clickable" onclick="window.location.href='{{ url('/assets') }}'">
                <div class="spread">
                    <span class="lbl">My Assets</span>

                    <span class="ico tint-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16v10H4z"></path>
                            <path d="M8 20h8M12 16v4"></path>
                        </svg>
                    </span>
                </div>

                <div style="margin-top:14px;display:flex;align-items:baseline;gap:6px">
                    <span style="font-size:28px;font-weight:800" class="tc-num">
                        {{ $assetStats['total'] }}
                    </span>

                    <span style="font-size:13px;color:#94A3B8;font-weight:600">
                        assigned
                    </span>
                </div>

                <div
                    style="font-size:12.5px;color:#94A3B8;margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $assetStats['names'] }}
                </div>
            </div>
        </div>



        {{-- ATTENDANCE LOGS --}}
        <div class="card" style="margin-top:16px">
            <div class="spread" style="padding:16px 18px 12px">
                <div>
                    <span class="section-title">Today Check In / Check Out Logs</span>

                    <div style="font-size:12.5px;color:#94A3B8;margin-top:3px">
                        Session logs show check-in time, check-out time and exact duration with milliseconds.
                    </div>
                </div>

                <span class="badge xs">
                    {{ $todayAttendances->count() }} logs
                </span>
            </div>

            <div id="attLogsWrap" style="padding:0 18px 18px">
                @forelse ($todayAttendances as $index => $attendance)
                    @php
                        $checkIn =
                            $attendance->check_in_at ?? ($attendance->check_in ?? ($attendance->in_time ?? null));
                        $checkOut =
                            $attendance->check_out_at ?? ($attendance->check_out ?? ($attendance->out_time ?? null));

                        try {
                            $checkInTime = $checkIn ? \Carbon\Carbon::parse($checkIn) : null;
                        } catch (\Throwable $e) {
                            $checkInTime = null;
                        }

                        try {
                            $checkOutTime = $checkOut ? \Carbon\Carbon::parse($checkOut) : null;
                        } catch (\Throwable $e) {
                            $checkOutTime = null;
                        }

                        $durationText = 'Running';

                        if ($checkInTime && $checkOutTime) {
                            $workedHour = $attendance->worked_hour ?? null;

                            if ($workedHour !== null && is_numeric($workedHour)) {
                                $totalMs = (int) round(((float) $workedHour) * 60 * 60 * 1000);
                            } else {
                                $totalMs = $checkOutTime->diffInMilliseconds($checkInTime);
                            }

                            $hours = floor($totalMs / 3600000);
                            $totalMs = $totalMs % 3600000;

                            $minutes = floor($totalMs / 60000);
                            $totalMs = $totalMs % 60000;

                            $seconds = floor($totalMs / 1000);
                            $milliseconds = $totalMs % 1000;

                            $durationText =
                                $hours .
                                'h ' .
                                str_pad($minutes, 2, '0', STR_PAD_LEFT) .
                                'm ' .
                                str_pad($seconds, 2, '0', STR_PAD_LEFT) .
                                's ' .
                                str_pad($milliseconds, 3, '0', STR_PAD_LEFT) .
                                'ms';
                        }
                    @endphp

                    <div
                        style="display:grid;grid-template-columns:72px 1fr 1fr 1fr auto;gap:12px;align-items:center;padding:14px 0;border-top:1px solid #E2E8F0">
                        <div>
                            <div style="font-size:12px;color:#94A3B8;font-weight:700">Session</div>
                            <div style="font-size:16px;font-weight:800;color:#1E293B">#{{ $index + 1 }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px;color:#94A3B8;font-weight:700">Check In</div>
                            <div style="font-size:13.5px;font-weight:700;color:#1E293B">
                                {{ $checkInTime ? $checkInTime->format('h:i:s A') : '—' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:12px;color:#94A3B8;font-weight:700">Check Out</div>
                            <div style="font-size:13.5px;font-weight:700;color:#1E293B">
                                {{ $checkOutTime ? $checkOutTime->format('h:i:s A') : '—' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:12px;color:#94A3B8;font-weight:700">Duration</div>
                            <div style="font-size:13.5px;font-weight:800;color:#1E293B">
                                {{ $durationText }}
                            </div>
                        </div>

                        <span class="badge {{ $checkOutTime ? 'badge-green' : 'badge-blue' }}">
                            {{ $checkOutTime ? 'Completed' : 'Running' }}
                        </span>
                    </div>
                @empty
                    <div style="padding:18px;color:#94A3B8;font-size:13px">
                        No attendance log found today.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RECENT ACTIVITY / BOTTOM GRID --}}
        <div class="dashboard-bottom-grid" style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-top:16px">

            {{-- LATEST NOTICES --}}
            <div class="card">
                <div class="spread" style="padding:16px 18px 12px">
                    <span class="section-title">Latest Notices</span>
                    <a class="link" style="font-size:12.5px" href="{{ url('/notices') }}">View all</a>
                </div>

                @forelse ($notices->take(4) as $notice)
                    <div class="list-row" onclick="window.location.href='{{ url('/notices') }}'">
                        <span class="dot {{ !empty($notice['read']) ? 'off' : 'on' }}"></span>

                        <div style="flex:1;min-width:0">
                            <div
                                style="font-size:13.5px;font-weight:{{ !empty($notice['read']) ? 600 : 800 }};color:#1E293B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $notice['title'] }}
                            </div>

                            <div style="font-size:12px;color:#94A3B8;margin-top:2px">
                                {{ $notice['category'] }} · {{ $notice['date'] }}
                            </div>
                        </div>

                        <span class="badge">
                            {{ $notice['priority'] }}
                        </span>
                    </div>
                @empty
                    <div style="padding:18px;color:#94A3B8;font-size:13px">
                        No notices found.
                    </div>
                @endforelse
            </div>

            <div style="display:flex;flex-direction:column;gap:16px">

                {{-- NEXT MEETING --}}
                <div class="card card-pad-lg">
                    <div class="spread" style="margin-bottom:10px">
                        <span class="section-title">Next Meeting</span>
                        <a class="link" style="font-size:12.5px" href="{{ url('/meetings') }}">All</a>
                    </div>

                    <div style="font-size:14px;font-weight:700;color:#1E293B">
                        {{ $nextMeeting['title'] }}
                    </div>

                    <div style="font-size:12.5px;color:#94A3B8;margin-top:4px">
                        {{ $nextMeeting['time'] ?: 'No meeting time found' }}
                    </div>

                    @if (!empty($nextMeeting['link']))
                        <a class="btn btn-block" style="margin-top:12px;background:#16A34A;color:#fff"
                            href="{{ $nextMeeting['link'] }}" target="_blank">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 7l-7 5 7 5V7z"></path>
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                            </svg>
                            Join meeting
                        </a>
                    @else
                        <a class="btn btn-block" style="margin-top:12px;background:#16A34A;color:#fff"
                            href="{{ url('/meetings') }}">
                            View meetings
                        </a>
                    @endif
                </div>

                {{-- UPCOMING HOLIDAY --}}
                <div class="holiday-card">
                    <div class="holiday-kicker">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V8l7-5 7 5v13"></path>
                            <path d="M9 21v-6h6v6"></path>
                        </svg>
                        Upcoming Holiday
                    </div>

                    <div style="font-size:17px;font-weight:800;color:#7A3D12;margin-top:8px">
                        {{ $upcomingHoliday['title'] }}
                    </div>

                    <div style="font-size:13px;color:#A85C2A;margin-top:2px">
                        {{ $upcomingHoliday['date'] }}
                    </div>

                    <div style="font-size:12.5px;color:#C07B45;margin-top:8px;font-weight:600">
                        {{ $upcomingHoliday['remaining'] }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            "use strict";

            let started = false;

            let SERVER_SESSIONS = @json($jsTodaySessions);
            let dashboardSessions = Array.isArray(SERVER_SESSIONS) ? SERVER_SESSIONS.slice() : [];
            const ATTENDANCE_ENDPOINTS = {
                checkIn: @json(route('attendance.check-in')),
                checkOut: @json(route('attendance.check-out')),
                csrf: @json(csrf_token())
            };

            const SERVER_RULES = {
                showAttendanceActions: @json($showAttendanceActions),
                canCheckIn: @json($canCheckIn),
                canCheckOut: @json($canCheckOut),
                checkInMessage: @json(data_get($attendanceRules, 'messages.check_in')),
                checkOutMessage: @json(data_get($attendanceRules, 'messages.check_out')),
                isFullDayLeave: @json(data_get($attendanceRules, 'leave.is_full_day_leave', false)),
                isInsideShortLeave: @json(data_get($attendanceRules, 'leave.is_inside_short_leave', false)),
                shortLeaveWindows: @json(data_get($attendanceRules, 'leave.short_leave_windows', []))
            };

            const SETTINGS_KEY = "tc_attendance_rules";

            const DEFAULT_SETTINGS = {
                start: @json($officeOpeningInput),
                end: @json($officeClosingInput),
                max: Number(@json($maxCheckIn))
            };

            function bootTC() {
                if (!window.TC || typeof TC.boot !== "function") return false;

                if (!TC.state) {
                    TC.boot(null);
                }

                if (!TC.state.att) {
                    TC.state.att = {};
                }

                if (!Array.isArray(TC.state.att.sessions)) {
                    TC.state.att.sessions = [];
                }

                const today = todayKey();

                TC.state.att.sessions = TC.state.att.sessions
                    .filter(function(session) {
                        return session.date !== today;
                    })
                    .concat(dashboardSessions);

                if (typeof TC.save === "function") {
                    TC.save();
                }

                return !!TC.state;
            }

            function loadSettings() {
                return {
                    start: DEFAULT_SETTINGS.start,
                    end: DEFAULT_SETTINGS.end,
                    max: DEFAULT_SETTINGS.max
                };
            }

            function saveSettings(settings) {
                // Rules come from database/server. Do not persist manual browser overrides.
                return settings;
            }

            function applySettingsToInputs() {
                const settings = loadSettings();

                const startInput = document.getElementById("attStartTime");
                const endInput = document.getElementById("attEndTime");
                const maxInput = document.getElementById("attMaxSessions");

                if (startInput) startInput.value = settings.start;
                if (endInput) endInput.value = settings.end;
                if (maxInput) maxInput.value = settings.max;
            }

            function readSettingsFromInputs() {
                const startInput = document.getElementById("attStartTime");
                const endInput = document.getElementById("attEndTime");
                const maxInput = document.getElementById("attMaxSessions");

                const settings = {
                    start: startInput && startInput.value ? startInput.value : DEFAULT_SETTINGS.start,
                    end: endInput && endInput.value ? endInput.value : DEFAULT_SETTINGS.end,
                    max: maxInput && maxInput.value ? Math.max(1, Number(maxInput.value)) : DEFAULT_SETTINGS.max
                };

                saveSettings(settings);

                return settings;
            }

            function todayKey() {
                const d = new Date();

                return (
                    d.getFullYear() +
                    "-" +
                    String(d.getMonth() + 1).padStart(2, "0") +
                    "-" +
                    String(d.getDate()).padStart(2, "0")
                );
            }

            function timeToMinutes(time) {
                const parts = String(time || "00:00").split(":");

                return (Number(parts[0] || 0) * 60) + Number(parts[1] || 0);
            }

            function nowMinutes() {
                const d = new Date();

                return d.getHours() * 60 + d.getMinutes();
            }

            function isWithinAllowedTime(settings) {
                const start = timeToMinutes(settings.start);
                const end = timeToMinutes(settings.end);
                const now = nowMinutes();

                if (start <= end) {
                    return now >= start && now <= end;
                }

                return now >= start || now <= end;
            }

            function msToText(totalMs) {
                totalMs = Math.max(0, Math.floor(Number(totalMs || 0)));

                const hours = Math.floor(totalMs / 3600000);
                totalMs = totalMs % 3600000;

                const minutes = Math.floor(totalMs / 60000);
                totalMs = totalMs % 60000;

                const seconds = Math.floor(totalMs / 1000);
                const milliseconds = totalMs % 1000;

                return (
                    hours +
                    "h " +
                    String(minutes).padStart(2, "0") +
                    "m " +
                    String(seconds).padStart(2, "0") +
                    "s " +
                    String(milliseconds).padStart(3, "0") +
                    "ms"
                );
            }

            function getTodaySessions() {
                if (!Array.isArray(dashboardSessions)) dashboardSessions = [];

                const today = todayKey();

                return dashboardSessions.filter(function(session) {
                    return session.date === today;
                });
            }

            function getActiveSession() {
                const sessions = getTodaySessions();

                for (let i = sessions.length - 1; i >= 0; i--) {
                    if (!sessions[i].outEpochMs) {
                        return sessions[i];
                    }
                }

                return null;
            }

            function getTotalTodayMs() {
                const sessions = getTodaySessions();
                const now = Date.now();

                return sessions.reduce(function(total, session) {
                    if (session.outEpochMs) {
                        return total + Number(session.durationMs || 0);
                    }

                    if (session.inEpochMs) {
                        return total + Math.max(0, now - Number(session.inEpochMs));
                    }

                    return total;
                }, 0);
            }

            function countCompletedCheckouts() {
                return getTodaySessions().filter(function(session) {
                    return !!session.outEpochMs;
                }).length;
            }

            function syncSessionsFromServer(sessions) {
                SERVER_SESSIONS = Array.isArray(sessions) ? sessions : [];
                dashboardSessions = SERVER_SESSIONS.slice();

                if (!bootTC()) return;

                const today = todayKey();

                TC.state.att.sessions = TC.state.att.sessions
                    .filter(function(session) {
                        return session.date !== today;
                    })
                    .concat(dashboardSessions);

                if (typeof TC.save === "function") {
                    TC.save();
                }
            }

            function setAttendanceButtons(active, label, actionUrl) {
                document.querySelectorAll("[data-attendance-form]").forEach(function(form) {
                    form.action = actionUrl;
                });

                const heroAttBtn = document.getElementById("heroAttBtn");
                const cardAttBtn = document.getElementById("cardAttBtn");
                const topAtt = document.getElementById("topAtt");
                const topAttLabel = document.getElementById("topAttLabel");

                if (heroAttBtn) heroAttBtn.textContent = label;

                if (cardAttBtn) {
                    cardAttBtn.textContent = label;
                    cardAttBtn.className = "btn " + (active ? "btn-ghost" : "btn-primary") + " btn-block";
                }

                if (topAtt) {
                    topAtt.className = "att-btn " + (active ? "in" : "out");
                }

                if (topAttLabel) {
                    topAttLabel.textContent = label;
                }
            }

            function applyAttendanceState(payload) {
                const active = !!payload.has_open_attendance;
                SERVER_RULES.canCheckIn = !active;
                SERVER_RULES.canCheckOut = active;

                if (payload.permissions) {
                    SERVER_RULES.canCheckIn = !!payload.permissions.can_check_in;
                    SERVER_RULES.canCheckOut = !!payload.permissions.can_check_out;
                }

                if (payload.messages) {
                    SERVER_RULES.checkInMessage = payload.messages.check_in || SERVER_RULES.checkInMessage;
                    SERVER_RULES.checkOutMessage = payload.messages.check_out || SERVER_RULES.checkOutMessage;
                }

                syncSessionsFromServer(payload.sessions || []);

                setAttendanceButtons(
                    active,
                    payload.action_label || (active ? "Check Out" : "Check In"),
                    payload.action_url || (active ? ATTENDANCE_ENDPOINTS.checkOut : ATTENDANCE_ENDPOINTS.checkIn)
                );

                renderLiveAttendance();

                if (payload.message) {
                    notify(payload.message);
                }
            }

            function submitAttendance(form) {
                const button = form.querySelector("button");

                const allowed = canPerformAttendanceAction();

                if (!allowed.ok) {
                    notify(allowed.message);
                    return;
                }

                if (button) {
                    button.disabled = true;
                }

                fetch(form.action, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": ATTENDANCE_ENDPOINTS.csrf
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error("Attendance request failed.");
                    }

                    return response.json();
                }).then(function(payload) {
                    applyAttendanceState(payload);
                }).catch(function() {
                    notify("Attendance could not be saved. Please try again.");
                }).finally(function() {
                    if (button) {
                        button.disabled = false;
                    }
                });
            }

            function canPerformAttendanceAction() {
                bootTC();

                if (!SERVER_RULES.showAttendanceActions || SERVER_RULES.isFullDayLeave) {
                    return {
                        ok: false,
                        message: SERVER_RULES.checkInMessage || "You are on approved leave today."
                    };
                }

                const settings = readSettingsFromInputs();
                const sessions = getTodaySessions();
                const active = getActiveSession();
                const completedCheckouts = countCompletedCheckouts();

                if (!isWithinAllowedTime(settings)) {
                    return {
                        ok: false,
                        message: active ?
                            (SERVER_RULES.checkOutMessage || "Check out is only allowed during office time.") : (
                                SERVER_RULES.checkInMessage || "Check in is only allowed during office time.")
                    };
                }

                if (active) {
                    if (!SERVER_RULES.canCheckOut) {
                        return {
                            ok: false,
                            message: SERVER_RULES.checkOutMessage || "Check out is not allowed right now."
                        };
                    }

                    if (completedCheckouts >= settings.max) {
                        return {
                            ok: false,
                            message: "Maximum " + settings.max + " check-outs allowed for today."
                        };
                    }

                    return {
                        ok: true,
                        message: ""
                    };
                }

                if (!SERVER_RULES.canCheckIn) {
                    return {
                        ok: false,
                        message: SERVER_RULES.checkInMessage || "Check in is not allowed right now."
                    };
                }

                if (sessions.length >= settings.max) {
                    return {
                        ok: false,
                        message: "Maximum " + settings.max + " check-ins allowed for today."
                    };
                }

                return {
                    ok: true,
                    message: ""
                };
            }

            function notify(message) {
                if (window.TC && typeof TC.toast === "function") {
                    TC.toast(message);
                    return;
                }

                const root = document.getElementById("toastRoot");

                if (!root) {
                    alert(message);
                    return;
                }

                root.innerHTML = `
                    <div class="toast">
                        <span class="ok">✓</span>
                        ${message}
                    </div>
                `;

                clearTimeout(window.__attRuleToast);

                window.__attRuleToast = setTimeout(function() {
                    root.innerHTML = "";
                }, 2600);
            }

            function renderLiveAttendance() {
                bootTC();

                const settings = loadSettings();
                const sessions = getTodaySessions();
                const active = getActiveSession();
                const totalText = msToText(getTotalTodayMs());
                const completedCheckouts = countCompletedCheckouts();
                const allowedNow = isWithinAllowedTime(settings);

                const label = active ? "Check Out" : "Check In";

                const status = active ?
                    "Checked in" :
                    sessions.length ?
                    "Checked out" :
                    "Not checked in";

                const summary = active ?
                    "You checked in at " + active.inTime + ". Total today: " + totalText + "." :
                    sessions.length ?
                    "You are currently checked out. Total today: " + totalText + "." :
                    "You haven't checked in yet — have a great day!";

                const sub = active ?
                    "Current session started at " + active.inTime + " · Sessions today: " + sessions.length :
                    sessions.length ?
                    sessions.length + " sessions completed today" :
                    "Tap check in to start your day";

                const sessionSub = active ?
                    "One session is currently running" :
                    sessions.length ?
                    "Last session completed" :
                    "No attendance log found today";

                let ruleStatus = allowedNow ?
                    "Allowed now · " + Math.max(0, settings.max - sessions.length) + " check-ins left · " + Math.max(0,
                        settings.max - completedCheckouts) + " check-outs left" :
                    "Not allowed now · Allowed from " + settings.start + " to " + settings.end;

                if (!SERVER_RULES.showAttendanceActions || SERVER_RULES.isFullDayLeave) {
                    ruleStatus = SERVER_RULES.checkInMessage || "You are on approved leave today.";
                } else if (SERVER_RULES.isInsideShortLeave) {
                    ruleStatus = SERVER_RULES.checkInMessage || "You are inside approved short leave time.";
                }

                const attSummary = document.getElementById("attSummary");
                const heroAttBtn = document.getElementById("heroAttBtn");
                const attCardValue = document.getElementById("attCardValue");
                const attStatusBadge = document.getElementById("attStatusBadge");
                const attCardSub = document.getElementById("attCardSub");
                const cardAttBtn = document.getElementById("cardAttBtn");
                const attSessionCount = document.getElementById("attSessionCount");
                const attSessionSub = document.getElementById("attSessionSub");
                const topAtt = document.getElementById("topAtt");
                const topAttLabel = document.getElementById("topAttLabel");
                const attRuleStatus = document.getElementById("attRuleStatus");

                if (attSummary) attSummary.textContent = summary;
                if (heroAttBtn) heroAttBtn.textContent = label;
                if (attCardValue) attCardValue.textContent = totalText;
                if (attStatusBadge) attStatusBadge.textContent = status;
                if (attCardSub) attCardSub.textContent = sub;
                if (cardAttBtn) cardAttBtn.textContent = label;
                if (attSessionCount) attSessionCount.textContent = sessions.length;
                if (attSessionSub) attSessionSub.textContent = sessionSub;
                if (attRuleStatus) attRuleStatus.textContent = ruleStatus;

                const actionAllowed = canPerformAttendanceAction().ok;

                if (cardAttBtn) {
                    cardAttBtn.className = "btn " + (active ? "btn-ghost" : "btn-primary") + " btn-block";
                    cardAttBtn.disabled = !actionAllowed;
                    cardAttBtn.title = active ?
                        (SERVER_RULES.checkOutMessage || "") :
                        (SERVER_RULES.checkInMessage || "");
                }

                if (heroAttBtn) {
                    heroAttBtn.disabled = !actionAllowed;
                    heroAttBtn.title = active ?
                        (SERVER_RULES.checkOutMessage || "") :
                        (SERVER_RULES.checkInMessage || "");
                }

                if (topAtt) {
                    topAtt.className = "att-btn " + (active ? "in" : "out");
                }

                if (topAttLabel) {
                    topAttLabel.textContent = label;
                }

                renderAttendanceLogs(sessions);
            }

            function renderAttendanceLogs(sessions) {
                const wrap = document.getElementById("attLogsWrap");

                if (!wrap) return;

                if (!sessions.length) {
                    wrap.innerHTML = `
                        <div style="padding:18px;color:#94A3B8;font-size:13px">
                            No attendance log found today.
                        </div>
                    `;
                    return;
                }

                wrap.innerHTML = sessions.slice().reverse().map(function(session, index) {
                    const sessionNo = sessions.length - index;
                    const isRunning = !session.outEpochMs;

                    const duration = isRunning ?
                        msToText(Date.now() - Number(session.inEpochMs || Date.now())) :
                        msToText(Number(session.durationMs || 0));

                    return `
                        <div style="display:grid;grid-template-columns:72px 1fr 1fr 1fr auto;gap:12px;align-items:center;padding:14px 0;border-top:1px solid #E2E8F0">
                            <div>
                                <div style="font-size:12px;color:#94A3B8;font-weight:700">Session</div>
                                <div style="font-size:16px;font-weight:800;color:#1E293B">#${sessionNo}</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#94A3B8;font-weight:700">Check In</div>
                                <div style="font-size:13.5px;font-weight:700;color:#1E293B">${session.inTime || "—"}</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#94A3B8;font-weight:700">Check Out</div>
                                <div style="font-size:13.5px;font-weight:700;color:#1E293B">${session.outTime || "—"}</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#94A3B8;font-weight:700">Duration</div>
                                <div style="font-size:13.5px;font-weight:800;color:#1E293B">${duration}</div>
                            </div>

                            <span class="badge ${isRunning ? "badge-blue" : "badge-green"}">
                                ${isRunning ? "Running" : "Completed"}
                            </span>
                        </div>
                    `;
                }).join("");
            }

            function startLiveTimer() {
                if (started) return;
                started = true;

                renderLiveAttendance();

                setInterval(function() {
                    renderLiveAttendance();
                }, 1000);
            }

            document.addEventListener("DOMContentLoaded", function() {
                bootTC();
                applySettingsToInputs();

                const startInput = document.getElementById("attStartTime");
                const endInput = document.getElementById("attEndTime");
                const maxInput = document.getElementById("attMaxSessions");

                [startInput, endInput, maxInput].forEach(function(input) {
                    if (!input) return;

                    input.addEventListener("change", function() {
                        readSettingsFromInputs();
                        renderLiveAttendance();
                        notify("Attendance rules updated.");
                    });
                });

                document.querySelectorAll("[data-attendance-form]").forEach(function(form) {
                    form.addEventListener("submit", function(e) {
                        e.preventDefault();
                        submitAttendance(form);
                    });
                });

                startLiveTimer();
            });

            window.addEventListener("load", function() {
                bootTC();
                applySettingsToInputs();
                startLiveTimer();
            });
        })();
    </script>
@endpush
