@extends('layouts.app')

@section('title', 'TADA - Teamiy Connect')
@section('page', 'tada')
@section('page_title', 'TADA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tada.css') }}">

    <style>
        #tadaModalRoot {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        #tadaModalRoot .overlay {
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

        #tadaModalRoot .modal {
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
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">Travel and daily allowance</div>
                <div class="greet">My TADA Claims</div>
                <div class="summary">Submitted expenses and approval status.</div>
            </div>
        </section>

        @if (session('status'))
            <div class="card card-pad" style="margin-top:18px;color:#0F766E;background:#ECFDF5;border-color:#A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card card-pad" style="margin-top:18px;color:#B91C1C;background:#FEF2F2;border-color:#FECACA">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="cards-grid auto-150" style="margin-top:18px">
            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Total Claimed</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#1E293B" class="tc-num">
                    {{ number_format((float) ($tadaStats['total_claimed'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Approved</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#16A34A" class="tc-num">
                    {{ number_format((float) ($tadaStats['approved'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Pending</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#B26A00" class="tc-num">
                    {{ number_format((float) ($tadaStats['pending'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Paid</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#057DB0" class="tc-num">
                    {{ number_format((float) ($tadaStats['paid'] ?? 0), 2) }}
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:18px">
            <div class="spread flex-wrap" style="padding:16px 18px">
                <span class="section-title" style="margin-right:auto">TADA Claims</span>

                <button class="btn btn-primary btn-sm" type="button" id="openTadaModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Create TADA
                </button>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Expense</th>
                            <th>Status</th>
                            <th>Settled</th>
                            <th>Approved</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tadas as $tada)
                            <tr>
                                <td>{{ $tada->title }}</td>
                                <td style="max-width:260px;color:#64748B">{{ strip_tags($tada->description ?: '-') }}</td>
                                <td>{{ number_format((float) $tada->total_expense, 2) }}</td>
                                <td>@include('partials.status-badge', ['slot' => $tada->status])</td>
                                <td>{{ $tada->is_settled ? 'Yes' : 'No' }}</td>
                                <td>{{ optional($tada->approved_date)->format('d M Y') ?: '-' }}</td>
                                <td>{{ $tada->remark ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No TADA claims found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-pad">{{ $tadas->links() }}</div>
        </div>
    </div>

    <div id="tadaModalRoot" style="display:none">
        <div class="overlay" id="tadaModalOverlay">
            <div class="modal">
                <div class="modal-head">
                    <h3>Create TADA Claim</h3>

                    <button class="modal-x" type="button" id="closeTadaModal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('tada.store') }}">
                    @csrf

                    <div class="modal-body">
                        <label class="label">Claim title</label>
                        <input class="input" name="title" value="{{ old('title') }}" placeholder="e.g. Travel expense">

                        <label class="label" style="margin-top:14px">Total expense</label>
                        <input class="input" type="number" step="0.01" min="0" name="total_expense"
                            value="{{ old('total_expense') }}" placeholder="0.00">

                        <label class="label" style="margin-top:14px">Description</label>
                        <textarea class="textarea" name="description" rows="4" placeholder="Briefly describe this claim...">{{ old('description') }}</textarea>

                        <div class="dashed">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <path d="M17 8l-5-5-5 5"></path>
                                <path d="M12 3v12"></path>
                            </svg>
                            Attach receipt (optional)
                        </div>
                    </div>

                    <div class="modal-foot">
                        <button class="btn btn-ghost" type="button" data-close-tada-modal>
                            Cancel
                        </button>

                        <button class="btn btn-primary" type="submit">
                            Submit claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.TEAMIY_OPEN_TADA_MODAL = @json($errors->any());
    </script>
    <script src="{{ asset('js/tada.js') }}"></script>
@endpush
