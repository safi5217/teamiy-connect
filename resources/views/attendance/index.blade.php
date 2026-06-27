@extends('layouts.app')

@section('title', 'Attendance - Teamiy Connect')
@section('page', 'attendance')
@section('page_title', 'Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
    @php
        $today = $employee->todayAttendances()->latest('created_at')->get();
        $workedHours = $attendances->sum('worked_hour');
        $overtime = $attendances->sum('overtime');
    @endphp

    <div class="wrap">
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">{{ now()->format('l, d F Y') }}</div>
                <div class="greet">Attendance</div>
                <div class="summary">{{ data_get($attendanceRules, 'messages.check_in', 'Your attendance history from the HR database.') }}</div>
            </div>
            <div class="hero-actions">
                @if($today->whereNotNull('check_in_at')->whereNull('check_out_at')->isNotEmpty())
                    <form method="POST" action="{{ route('attendance.check-out') }}">
                        @csrf
                        <button class="hero-btn" type="submit">Check Out</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('attendance.check-in') }}">
                        @csrf
                        <button class="hero-btn" type="submit">Check In</button>
                    </form>
                @endif
            </div>
        </section>

        @if(session('status'))
            <div class="card card-pad" style="margin-top:18px;color:#0F766E;background:#ECFDF5;border-color:#A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        <div class="cards-grid auto-250" style="margin-top:18px">
            <div class="card card-pad stat"><div class="lbl">Today logs</div><div class="val tc-num">{{ $today->count() }}</div></div>
            <div class="card card-pad stat"><div class="lbl">Worked hours(in minutes)</div><div class="val tc-num">{{ number_format((float) $workedHours, 2) }}</div></div>
            <div class="card card-pad stat"><div class="lbl">Overtime</div><div class="val tc-num">{{ number_format((float) $overtime, 2) }}</div></div>
        </div>

        <div class="card" style="margin-top:18px">
            <div class="card-pad-lg spread">
                <h3 class="section-title">Attendance History</h3>
                <span class="badge badge-blue">{{ $attendances->total() }} records</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Worked</th><th>OT</th><th>UT</th></tr></thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ optional($attendance->attendance_date)->format('d M Y') ?? $attendance->attendance_date }}</td>
                                <td>{{ $attendance->check_in_at ?: '-' }}</td>
                                <td>{{ $attendance->check_out_at ?: '-' }}</td>
                                <td>{{ $attendance->worked_hour ?? '-' }}</td>
                                <td>{{ $attendance->overtime ?? '-' }}</td>
                                <td>{{ $attendance->undertime ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No attendance records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-pad">{{ $attendances->links() }}</div>
        </div>
    </div>
@endsection
