@extends('layouts.app')

@section('title', 'Notices - Teamiy Connect')
@section('page', 'notices')
@section('page_title', 'Notices')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notices.css') }}">
@endpush

@section('content')
    <div class="wrap">
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">Notice board</div>
                <div class="greet">Company Notices</div>
                <div class="summary">Notices assigned to your employee record.</div>
            </div>
        </section>

        <div class="cards-grid auto-150" style="margin-top:18px">
            <div class="card notice-stat-card">
                <span>Total Notices</span>
                <strong class="tc-num">{{ $noticeStats['total'] ?? 0 }}</strong>
            </div>

            <div class="card notice-stat-card">
                <span>Published</span>
                <strong class="tc-num text-green">{{ $noticeStats['published'] ?? 0 }}</strong>
            </div>

            <div class="card notice-stat-card">
                <span>This Month</span>
                <strong class="tc-num text-blue">{{ $noticeStats['this_month'] ?? 0 }}</strong>
            </div>

            <div class="card notice-stat-card">
                <span>Receivers</span>
                <strong class="tc-num text-orange">{{ $noticeStats['receivers'] ?? 0 }}</strong>
            </div>
        </div>

        <div class="spread flex-wrap" style="margin-top:22px">
            <span class="section-title">My Notices</span>
            <span class="badge badge-blue">{{ $notices->total() }} records</span>
        </div>

        <div class="notice-list" style="margin-top:14px">
            @forelse ($notices as $notice)
                <article class="card notice-card">
                    <div class="notice-date">
                        <div class="d">{{ $notice->notice_publish_date?->format('d') ?? '--' }}</div>
                        <div class="m">{{ $notice->notice_publish_date?->format('M') ?? '---' }}</div>
                    </div>

                    <div class="notice-body">
                        <div class="notice-head">
                            <div class="notice-title-block">
                                <h3>{{ $notice->title }}</h3>
                                <p>
                                    {{ $notice->company?->name ?? 'Company notice' }}
                                    @if ($notice->branch)
                                        &middot; {{ $notice->branch->name }}
                                    @endif
                                </p>
                            </div>

                            @include('partials.status-badge', ['slot' => $notice->is_active ? 'active' : 'inactive'])
                        </div>

                        @if ($notice->description)
                            <div class="notice-description">
                                {!! \Illuminate\Support\Str::limit(strip_tags($notice->description), 260) !!}
                            </div>
                        @endif

                        <div class="notice-section-label">Notice Details</div>
                        <div class="notice-details">
                            <div>
                                <span>Publish Date</span>
                                <strong>{{ $notice->notice_publish_date?->format('d M Y H:i') ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Branch</span>
                                <strong>{{ $notice->branch?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Created By</span>
                                <strong>{{ $notice->creator?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Created At</span>
                                <strong>{{ $notice->created_at?->format('d M Y H:i') ?? '-' }}</strong>
                            </div>
                        </div>

                        <div class="notice-section-label">Notice Receivers</div>
                        <div class="notice-receivers">
                            @forelse ($notice->receivers->take(8) as $receiver)
                                <div class="notice-receiver">
                                    <span>{{ \Illuminate\Support\Str::of($receiver->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') ?: 'U' }}</span>
                                    <div>
                                        <strong>{{ $receiver->name }}</strong>
                                        <small>{{ $receiver->work_email ?: $receiver->email }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="notice-receiver muted">No receivers assigned</div>
                            @endforelse
                        </div>

                        @if ($notice->receivers->count() > 8)
                            <div class="notice-more">+{{ $notice->receivers->count() - 8 }} more receivers</div>
                        @endif
                    </div>
                </article>
            @empty
                @include('partials.empty-state', ['message' => 'No notices found.'])
            @endforelse
        </div>

        @if ($notices->hasPages())
            <div style="margin-top:18px">{{ $notices->links() }}</div>
        @endif
    </div>
@endsection
