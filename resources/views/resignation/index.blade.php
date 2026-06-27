@extends('layouts.app')

@section('title', 'Resignation - Teamiy Connect')
@section('page', 'resignation')
@section('page_title', 'Resignation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/resignation.css') }}">

    <style>
        .resign-page {
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .resign-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: #FDEEE2;
            color: #C2691B;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .resign-note {
            background: #F8FAFC;
            border: 1px solid #EEF2F7;
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 18px;
            font-size: 13px;
            color: #64748B;
            line-height: 1.6;
        }

        .resign-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            margin-top: 16px;
            border: 1px solid #EEF2F7;
            border-radius: 12px;
            overflow: hidden;
        }

        .resign-info-grid>div {
            padding: 18px 24px;
            border-right: 1px solid #EEF2F7;
        }

        .resign-info-grid>div:last-child {
            border-right: 0;
        }

        .resign-k {
            font-size: 11.5px;
            font-weight: 800;
            color: #94A3B8;
            text-transform: uppercase;
        }

        .resign-v {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            margin-top: 5px;
        }

        .resign-danger {
            background: linear-gradient(135deg, #C0392B, #D9534F);
            color: #fff;
            border: 0;
        }

        .resign-step {
            display: grid;
            grid-template-columns: 28px 1fr;
            gap: 14px;
        }

        .resign-rail {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .resign-dot {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 2px solid #CBD5E1;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 900;
        }

        .resign-dot.done,
        .resign-dot.current {
            border-color: #34A853;
            background: #34A853;
        }

        .resign-line {
            width: 2px;
            min-height: 34px;
            background: #E2E8F0;
        }

        .resign-line.done {
            background: #34A853;
        }

        #resignationModalRoot {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        #resignationModalRoot .overlay {
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

        #resignationModalRoot .modal {
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
    @php
        $current = $latestResignation;
        $statusKey = strtolower((string) ($current->status ?? ''));
        $statusIndex = match ($statusKey) {
            'approved' => 3,
            'onreview', 'on_review', 'review' => 2,
            'pending' => 1,
            default => $current ? 1 : 0,
        };
        $noticeDays = $current
            ? max(0, (int) $current->resignation_date->diffInDays($current->last_working_day))
            : 30;
        $joinedDate = $employee->joining_date;
        $tenure = $joinedDate ? $joinedDate->diffForHumans(null, true) : '-';
        $managerName = $employee->supervisor->name ?? 'your manager';
        $steps = [
            ['title' => 'Resignation Submitted', 'sub' => 'You submit your notice'],
            ['title' => 'Manager Review', 'sub' => 'Awaiting acceptance from '.$managerName],
            ['title' => 'HR Approval & Notice', 'sub' => 'People team confirms your notice period'],
        ];
    @endphp

    <div class="resign-page">
        @if (session('status'))
            <div class="card card-pad" style="color:#0F766E;background:#ECFDF5;border-color:#A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card card-pad" style="color:#B91C1C;background:#FEF2F2;border-color:#FECACA">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card" style="padding:24px">
            <div class="row" style="gap:14px">
                <div class="resign-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <path d="M14 2v6h6"></path>
                        <path d="M9 13h6M9 17h4"></path>
                    </svg>
                </div>

                <div>
                    <div style="font-size:18px;font-weight:800;letter-spacing:-.01em">Submit your resignation</div>
                    <div style="font-size:13.5px;color:#64748B;margin-top:2px">
                        Start your formal exit process through Teamiy Connect.
                    </div>
                </div>
            </div>

            <div class="resign-note">
                Your contract requires a <strong style="color:#334155">{{ $noticeDays }}-day notice period</strong>.
                Once submitted, your manager and HR will review your request.
            </div>

            <div class="resign-info-grid">
                <div>
                    <div class="resign-k">Joined</div>
                    <div class="resign-v">{{ $joinedDate?->format('d M Y') ?? '-' }}</div>
                </div>
                <div>
                    <div class="resign-k">Tenure</div>
                    <div class="resign-v">{{ $tenure }}</div>
                </div>
                <div>
                    <div class="resign-k">Notice Period</div>
                    <div class="resign-v">{{ $noticeDays }} days</div>
                </div>
            </div>

            <button class="btn btn-danger resign-danger" style="margin-top:20px;font-size:14px;padding:12px 20px"
                type="button" id="openResignationModal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <path d="M14 2v6h6"></path>
                    <path d="M12 11v6M9 14h6"></path>
                </svg>
                Submit Resignation
            </button>
        </div>

        <div class="card" style="padding:22px">
            <div class="section-title" style="margin-bottom:18px">How it works</div>
            @foreach ($steps as $index => $step)
                @php
                    $stepNumber = $index + 1;
                    $isDone = $statusIndex > $stepNumber;
                    $isCurrent = $statusIndex === $stepNumber;
                    $isActive = $statusIndex >= $stepNumber;
                    $titleColor = $isActive ? '#1E293B' : '#94A3B8';
                @endphp
                <div class="resign-step">
                    <div class="resign-rail">
                        <div class="resign-dot {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
                            @if ($isActive)
                                ✓
                            @endif
                        </div>
                        @if (! $loop->last)
                            <div class="resign-line {{ $isDone ? 'done' : '' }}"></div>
                        @endif
                    </div>
                    <div style="padding-bottom:16px">
                        <div style="font-size:14px;font-weight:800;color:{{ $titleColor }}">{{ $step['title'] }}</div>
                        <div style="font-size:12.5px;color:#94A3B8;margin-top:2px">{{ $step['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="spread flex-wrap" style="padding:16px 18px">
                <span class="section-title" style="margin-right:auto">Resignation Requests</span>
                <span class="badge badge-blue">{{ $resignations->count() }} records</span>
            </div>
            <div class="table-wrap">
                <table class="table" style="min-width:860px">
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Last Working Day</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Admin Remark</th>
                            <th>Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resignations as $resignation)
                            <tr>
                                <td>{{ optional($resignation->resignation_date)->format('d M Y') }}</td>
                                <td>{{ optional($resignation->last_working_day)->format('d M Y') }}</td>
                                <td>@include('partials.status-badge', ['slot' => $resignation->status])</td>
                                <td style="max-width:240px;color:#64748B">{{ $resignation->reason ?: '-' }}</td>
                                <td style="max-width:220px;color:#64748B">{{ $resignation->admin_remark ?: '-' }}</td>
                                <td>
                                    @if ($resignation->document)
                                        <a class="link" href="{{ asset('storage/'.$resignation->document) }}" target="_blank">View</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No resignation requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($transfers->isNotEmpty() || $terminations->isNotEmpty())
            <div class="cards-grid auto-330">
                @foreach ($transfers as $transfer)
                    <div class="card card-pad">
                        <div class="spread"><strong>Transfer</strong>@include('partials.status-badge', ['slot' => $transfer->status])</div>
                        <div style="font-size:13px;color:#64748B;margin-top:8px">{{ optional($transfer->transfer_date)->format('d M Y') }}</div>
                        <div style="font-size:13px;color:#94A3B8;margin-top:4px">{{ $transfer->description }}</div>
                    </div>
                @endforeach
                @foreach ($terminations as $termination)
                    <div class="card card-pad">
                        <div class="spread"><strong>Termination</strong>@include('partials.status-badge', ['slot' => $termination->status])</div>
                        <div style="font-size:13px;color:#64748B;margin-top:8px">{{ optional($termination->termination_date)->format('d M Y') }}</div>
                        <div style="font-size:13px;color:#94A3B8;margin-top:4px">{{ $termination->reason }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div id="resignationModalRoot" style="display:none">
        <div class="overlay" id="resignationModalOverlay">
            <div class="modal">
                <div class="modal-head">
                    <h3>Submit Resignation</h3>
                    <button class="modal-x" type="button" id="closeResignationModal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('resignation.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div style="background:#FFF7EF;border:1px solid #F6DEC8;border-radius:11px;padding:11px 14px;font-size:12.5px;color:#A85C2A;line-height:1.55;margin-bottom:18px">
                            This starts your formal exit process. Your manager and HR will be notified.
                        </div>

                        <div class="grid-2">
                            <div>
                                <label class="label">Resignation date</label>
                                <input type="date" class="input" name="resignation_date"
                                    value="{{ old('resignation_date', now()->toDateString()) }}">
                            </div>
                            <div>
                                <label class="label">Last working day</label>
                                <input type="date" class="input" name="last_working_day"
                                    value="{{ old('last_working_day', now()->addDays(30)->toDateString()) }}">
                            </div>
                        </div>

                        <label class="label" style="margin-top:14px">Reason</label>
                        <textarea class="textarea" name="reason" rows="4" placeholder="Add your resignation reason...">{{ old('reason') }}</textarea>

                        <label class="label" style="margin-top:14px">Document (optional)</label>
                        <input type="file" class="input" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="modal-foot">
                        <button class="btn btn-ghost" type="button" data-close-resignation-modal>Cancel</button>
                        <button class="btn btn-danger resign-danger" type="submit">Submit Resignation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.TEAMIY_OPEN_RESIGNATION_MODAL = @json($errors->any());
    </script>
    <script src="{{ asset('js/resignation.js') }}"></script>
@endpush
