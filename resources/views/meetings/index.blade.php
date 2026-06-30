@extends('layouts.app')

@section('title', 'Meetings - Teamiy Connect')
@section('page', 'meetings')
@section('page_title', 'Meetings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/meetings.css') }}">
@endpush

@section('content')
    <div class="wrap">
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">Team calendar</div>
                <div class="greet">Meetings</div>
                <div class="summary">Meetings assigned to you directly or through your department.</div>
            </div>
        </section>

        <div class="cards-grid auto-150" style="margin-top:18px">
            <div class="card meeting-stat-card">
                <span>Total Meetings</span>
                <strong class="tc-num">{{ $meetingStats['total'] ?? 0 }}</strong>
            </div>

            <div class="card meeting-stat-card">
                <span>Can Join</span>
                <strong class="tc-num text-green">{{ $meetingStats['upcoming'] ?? 0 }}</strong>
            </div>

            <div class="card meeting-stat-card">
                <span>Completed</span>
                <strong class="tc-num text-blue">{{ $meetingStats['completed'] ?? 0 }}</strong>
            </div>

            <div class="card meeting-stat-card">
                <span>Departments</span>
                <strong class="tc-num text-orange">{{ $meetingStats['departments'] ?? 0 }}</strong>
            </div>
        </div>

        <div class="spread flex-wrap" style="margin-top:22px">
            <span class="section-title">My Meetings</span>
            <span class="badge badge-blue">{{ $meetings->total() }} records</span>
        </div>

        <div class="meeting-list" style="margin-top:14px">
            @forelse ($meetings as $meeting)
                @php
                    $scheduledAt = $meeting->scheduledAt();
                    $isCompleted = $meeting->isCompleted();
                    $image = ltrim((string) ($meeting->image ?? ''), '/');
                    $imageUrl = null;

                    if ($image !== '') {
                        $imageUrl = str_starts_with($image, 'http://') || str_starts_with($image, 'https://')
                            ? $image
                            : asset(str_starts_with($image, 'storage/') || str_starts_with($image, 'assets/') ? $image : 'storage/'.$image);
                    }
                @endphp

                <article class="card meeting-card">
                    <div class="meeting-date">
                        <div class="d">{{ $meeting->meeting_date?->format('d') ?? '--' }}</div>
                        <div class="m">{{ $meeting->meeting_date?->format('M') ?? '---' }}</div>
                    </div>

                    <div class="meeting-body">
                        <div class="meeting-head">
                            <div class="meeting-title-block">
                                <h3>{{ $meeting->title }}</h3>
                                <p>
                                    {{ $meeting->venue ?: 'No venue' }}
                                    @if ($scheduledAt)
                                        &middot; {{ $scheduledAt->format('h:i A') }}
                                    @endif
                                </p>
                            </div>

                            @include('partials.status-badge', ['slot' => $isCompleted ? 'completed' : 'scheduled'])
                        </div>

                        @if ($imageUrl)
                            <img class="meeting-image" src="{{ $imageUrl }}" alt="{{ $meeting->title }}">
                        @endif

                        @if ($meeting->description)
                            <div class="meeting-description">
                                {!! \Illuminate\Support\Str::limit(strip_tags($meeting->description), 220) !!}
                            </div>
                        @endif

                        <div class="meeting-section-label">Meeting Details</div>
                        <div class="meeting-details">
                            <div>
                                <span>Meeting ID</span>
                                <strong>{{ $meeting->id }}</strong>
                            </div>
                            <div>
                                <span>Date</span>
                                <strong>{{ $meeting->meeting_date?->format('d M Y') ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Start Time</span>
                                <strong>{{ $scheduledAt?->format('h:i A') ?? $meeting->meeting_start_time ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Published</span>
                                <strong>{{ $meeting->meeting_published_at?->format('d M Y H:i') ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Company</span>
                                <strong>{{ $meeting->company?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Branch</span>
                                <strong>{{ $meeting->branch?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Created By</span>
                                <strong>{{ $meeting->creator?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Password</span>
                                <strong>{{ $meeting->meeting_password ?: '-' }}</strong>
                            </div>
                        </div>

                        <div class="meeting-section-label">Departments</div>
                        <div class="meeting-chip-row">
                            @forelse ($meeting->departments as $department)
                                <span class="meeting-chip">{{ $department->dept_name }}</span>
                            @empty
                                <span class="meeting-chip muted">No departments assigned</span>
                            @endforelse
                        </div>

                        <div class="meeting-section-label">Members Who Can Join</div>
                        <div class="meeting-members">
                            @forelse ($meeting->members->take(6) as $member)
                                <div class="meeting-member">
                                    <span>{{ \Illuminate\Support\Str::of($member->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') ?: 'U' }}</span>
                                    <div>
                                        <strong>{{ $member->name }}</strong>
                                        <small>{{ $member->work_email ?: $member->email }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="meeting-member muted">No direct members assigned</div>
                            @endforelse
                        </div>

                        @if ($meeting->members->count() > 6)
                            <div class="meeting-more">+{{ $meeting->members->count() - 6 }} more members</div>
                        @endif

                        <div class="meeting-actions">
                            @if ($isCompleted)
                                <span class="meeting-completed">Completed</span>
                            @elseif ($meeting->meeting_link)
                                <a class="btn btn-primary" href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener">
                                    Join Meeting
                                </a>
                            @else
                                <span class="meeting-completed muted">Meeting link not available</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                @include('partials.empty-state', ['message' => 'No meetings found.'])
            @endforelse
        </div>

        @if ($meetings->hasPages())
            <div style="margin-top:18px">{{ $meetings->links() }}</div>
        @endif
    </div>
@endsection






