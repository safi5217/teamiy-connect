@extends('layouts.app')

@section('title', 'Leave Management · Teamiy Connect')
@section('page', 'leave')
@section('page_title', 'Leave Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">

    <style>
        .leave-modal-tabs {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding: 4px;
            background: #F1F5F9;
            border-radius: 14px;
        }

        .leave-tab-btn {
            flex: 1;
            border: 0;
            background: transparent;
            color: #64748B;
            font-size: 13px;
            font-weight: 800;
            padding: 10px 12px;
            border-radius: 11px;
            cursor: pointer;
            transition: .2s ease;
        }

        .leave-tab-btn.active {
            background: #fff;
            color: #1E293B;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .leave-panel {
            display: none;
        }

        .leave-panel.active {
            display: block;
        }

        .leave-modal-hint {
            font-size: 12.5px;
            color: #94A3B8;
            margin-top: 6px;
            line-height: 1.6;
        }

        #leaveModalRoot {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        #leaveModalRoot .overlay {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100vh;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        #leaveModalRoot .modal {
            width: min(520px, 100%);
            max-height: calc(100vh - 48px);
            overflow-y: auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.24);
        }
    </style>
@endpush

@section('content')


    <div class="wrap">

        {{-- BALANCE CARDS --}}
        <div class="cards-grid auto-160" style="margin-bottom:20px">
            @foreach ($leavetype as $leavet)
                <div class="card" style="padding:16px">
                    <div style="font-size:12.5px;color:#64748B;font-weight:700">
                        {{ $leavet->name }}
                    </div>

                    <div style="display:flex;align-items:baseline;gap:5px;margin-top:6px">
                        <span style="font-size:24px;font-weight:800" class="tc-num">
                            {{ $leavet->used_leaves }}
                        </span>

                        <span style="font-size:12px;color:#94A3B8">
                            / {{ $leavet->left_leaves }} left
                        </span>
                    </div>

                    <div class="progress" style="margin-top:8px">
                        <div class="progress-bar bar-blue" style="width:{{ min($leavet->used_percentage, 100) }}%">
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Pending</div>

                <div style="display:flex;align-items:baseline;gap:5px;margin-top:6px">
                    <span style="font-size:24px;font-weight:800;color:#B26A00" class="tc-num">1</span>
                    <span style="font-size:12px;color:#94A3B8">requests</span>
                </div>

                <div style="font-size:11.5px;color:#94A3B8;margin-top:10px">
                    Awaiting approval
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="card"
                style="padding:14px 16px;margin-bottom:16px;color:#0F766E;background:#ECFDF5;border:1px solid #A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card"
                style="padding:14px 16px;margin-bottom:16px;color:#B91C1C;background:#FEF2F2;border:1px solid #FECACA">
                {{ $errors->first() }}
            </div>
        @endif
        {{-- LEAVE HISTORY --}}
        <div class="card">
            <div class="spread flex-wrap" style="padding:16px 18px">
                <span class="section-title" style="margin-right:auto">Leave History</span>

                <select class="select" id="leaveFilter" style="width:auto">
                    <option value="All">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <button class="btn btn-primary btn-sm" type="button" id="openLeaveModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Request Leave
                </button>
            </div>

            <div class="table-wrap">
                <table class="table" style="min-width:760px">
                    <thead>
                        <tr>
                            <th>TYPE</th>
                            <th>DATES</th>
                            <th>DURATION</th>
                            <th>REASON</th>
                            <th>STATUS</th>
                            <th>REMARKS</th>
                        </tr>
                    </thead>

                    <tbody id="leaveTableBody">
                        @forelse ($leavehistory as $leave)
                            <tr data-status="{{ ucfirst($leave->status) }}">
                                <td>
                                    <div style="font-size:13.5px;font-weight:700;color:#1E293B">
                                        {{ $leave->record_type === 'short' ? 'Short Leave' : $leave->leaveType->name ?? 'Leave Type' }}
                                    </div>
                                    <div style="font-size:11.5px;color:#94A3B8">
                                        {{ $leave->record_type === 'short' ? 'Short Leave' : $leave->title ?? 'Full Day' }}
                                    </div>
                                </td>

                                <td class="tc-num">
                                    @if ($leave->record_type === 'short')
                                        {{ optional($leave->issue_date)->format('d M Y') }}
                                    @else
                                        {{ optional($leave->leave_from)->format('d M Y') }} -
                                        {{ optional($leave->leave_to)->format('d M Y') }}
                                    @endif
                                </td>

                                <td class="tc-num" style="font-weight:600">
                                    @if ($leave->record_type === 'short')
                                        {{ $leave->start_time }} - {{ $leave->end_time }}
                                    @else
                                        {{ $leave->no_of_days }} {{ $leave->no_of_days > 1 ? 'days' : 'day' }}
                                    @endif
                                </td>

                                <td style="color:#64748B;max-width:200px">
                                    {{ $leave->reasons }}
                                </td>

                                <td>
                                    <span class="badge {{ strtolower($leave->status) }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>

                                <td style="color:#94A3B8;max-width:170px;font-size:12.5px">
                                    {{ $leave->admin_remark ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;color:#94A3B8;padding:20px">
                                    No leave history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- REQUEST LEAVE MODAL --}}
    <div id="leaveModalRoot" style="display:none">
        <div class="overlay" id="leaveModalOverlay">
            <div class="modal">

                <div class="modal-head">
                    <h3>Request Leave</h3>

                    <button class="modal-x" type="button" id="closeLeaveModal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- TABS --}}
                    <div style="display:flex;gap:10px;margin-bottom:16px">
                        <button class="btn btn-primary btn-block" type="button" data-leave-tab="full">
                            Full Day
                        </button>

                        <button class="btn btn-ghost btn-block" type="button" data-leave-tab="short">
                            Short Leave
                        </button>
                    </div>

                    {{-- FULL DAY FORM --}}
                    <form id="fullLeavePanel" action="{{ route('leave.store') }}" method="POST" data-leave-panel="full">
                        @csrf

                        <input type="hidden" name="mode" value="full_day">

                        <div class="grid-2">
                            <div>
                                <label class="label">Leave type</label>
                                <select class="select" name="leave_type_id">
                                    @foreach ($leavetype as $leavet)
                                        <option value="{{ $leavet->id }}">{{ $leavet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="label">Day type</label>
                                <select class="select" name="day_type">
                                    <option value="Full Day">Full Day</option>
                                    <option value="Half Day - First Half">Half Day - First Half</option>
                                    <option value="Half Day - Second Half">Half Day - Second Half</option>
                                    <option value="Multi Day">Multi Day</option>
                                </select>
                            </div>

                            <div>
                                <label class="label">Start date</label>
                                <input type="date" class="input" name="leave_from" value="2026-06-24">
                            </div>

                            <div>
                                <label class="label">End date</label>
                                <input type="date" class="input" name="leave_to" value="2026-06-24">
                            </div>
                        </div>

                        <label class="label" style="margin-top:14px">
                            Reason
                        </label>

                        <textarea class="textarea" name="reasons" placeholder="Briefly describe your reason…" rows="3"></textarea>

                        <div class="dashed">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <path d="M17 8l-5-5-5 5"></path>
                                <path d="M12 3v12"></path>
                            </svg>
                            Attach document (optional)
                        </div>

                        <div class="modal-foot">
                            <button class="btn btn-ghost" type="button" data-close-leave-modal>
                                Cancel
                            </button>

                            <button class="btn btn-primary" type="submit">
                                Submit request
                            </button>
                        </div>
                    </form>

                    {{-- SHORT LEAVE FORM --}}
                    <form id="shortLeavePanel" action="{{ route('leave.time-leave.store') }}" method="POST"
                        data-leave-panel="short" style="display:none">
                        @csrf

                        <input type="hidden" name="mode" value="short_leave">

                        <div class="grid-2">
                            <div>
                                <label class="label">Leave type</label>
                                <select class="select" name="leave_type">
                                    <option value="Short Leave">Short Leave</option>
                                </select>
                            </div>

                            <div>
                                <label class="label">Date</label>
                                <input type="date" class="input" name="issue_date" value="2026-06-24">
                            </div>

                            <div>
                                <label class="label">From time</label>
                                <input type="time" class="input" name="start_time" value="14:00">
                            </div>

                            <div>
                                <label class="label">To time</label>
                                <input type="time" class="input" name="end_time" value="16:00">
                            </div>
                        </div>

                        <label class="label" style="margin-top:14px">
                            Reason
                        </label>

                        <textarea class="textarea" name="reasons" placeholder="Briefly describe your reason…" rows="3"></textarea>

                        <div class="dashed">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <path d="M17 8l-5-5-5 5"></path>
                                <path d="M12 3v12"></path>
                            </svg>
                            Attach document (optional)
                        </div>

                        <div class="modal-foot">
                            <button class="btn btn-ghost" type="button" data-close-leave-modal>
                                Cancel
                            </button>

                            <button class="btn btn-primary" type="submit">
                                Submit request
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/leave.js') }}"></script>
@endpush
