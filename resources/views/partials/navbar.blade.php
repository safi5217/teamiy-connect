@php
    $topbarAttendanceRules = function_exists('attendance_rules') ? attendance_rules() : [];
    $topbarAttendances = collect(data_get($topbarAttendanceRules, 'full_details.attendance_details.today', []));
    $topbarHasOpenAttendance = $topbarAttendances
        ->whereNotNull('check_in_at')
        ->whereNull('check_out_at')
        ->isNotEmpty();
@endphp

<header class="topbar">
    <button class="icon-btn" data-action="toggle-nav">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M3 6h18M3 12h18M3 18h18"></path>
        </svg>
    </button>

    <div class="grow">
        <h2 id="viewTitle">@yield('page_title', 'Dashboard')</h2>
    </div>

    <form method="POST" action="{{ $topbarHasOpenAttendance ? route('attendance.check-out') : route('attendance.check-in') }}"
        style="display:inline" data-attendance-form>
        @csrf
        <button class="att-btn {{ $topbarHasOpenAttendance ? 'in' : 'out' }}" id="topAtt" type="submit" data-db-attendance>
            <span class="pulse"></span>
            <span id="topAttLabel">{{ $topbarHasOpenAttendance ? 'Check Out' : 'Check In' }}</span>
        </button>
    </form>
</header>
