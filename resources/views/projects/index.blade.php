@extends('layouts.app')

@section('title', 'Projects - Teamiy Connect')
@section('page', 'projects')
@section('page_title', 'Projects')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
    <style>
        .tc-pagination {
            margin-top: 18px;
        }

        .tc-pagination nav {
            display: flex;
            justify-content: center;
        }

        .tc-pagination a,
        .tc-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 14px;
            margin: 0 4px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .tc-pagination a:hover {
            background: #F47B26;
            border-color: #F47B26;
            color: #FFFFFF;
        }

        .tc-pagination span[aria-current="page"] span {
            background: #F47B26;
            border-color: #F47B26;
            color: #FFFFFF;
        }

        .tc-pagination span[aria-disabled="true"] span {
            opacity: .45;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="wrap">

        <div class="spread" style="margin-bottom:18px">
            <span class="section-title" style="margin-right:auto">All Projects</span>
        </div>

        <div class="cards-grid auto-330">
            @forelse($projects as $project)
                @php
                    $totalTasks = $project->tasks_count ?? 0;
                    $doneTasks = $project->tasks_done_count ?? 0;
                    $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                    $projectStatus = strtolower(str_replace(' ', '_', $project->status));

                    if ($progress >= 100) {
                        $displayStatus = 'Done';
                        $projectBadgeClass = 'badge-green';
                        $barClass = 'green';
                    } else {
                        $displayStatus = ucfirst(str_replace('_', ' ', $project->status));

                        $projectBadgeClass = match ($projectStatus) {
                            'completed', 'done' => 'badge-green',
                            'in_progress', 'active' => 'badge-blue',
                            'on_hold', 'pending', 'to_do' => 'badge-amber',
                            default => 'badge-gray',
                        };

                        $barClass = match ($projectStatus) {
                            'completed', 'done' => 'green',
                            'on_hold' => 'amber',
                            default => '',
                        };
                    }
                @endphp

                <div class="card card-pad clickable hover-pop"
                    onclick="window.location.href='{{ route('projects.show', $project->id) }}'" style="cursor:pointer">

                    <div class="spread" style="align-items:flex-start">
                        <div style="min-width:0">
                            <div
                                style="font-size:15.5px;font-weight:800;color:#1E293B;letter-spacing:-.01em;line-height:1.3">
                                {{ $project->name }}
                            </div>

                            <div style="font-size:12.5px;color:#94A3B8;margin-top:2px">
                                Project
                            </div>
                        </div>

                        <span class="badge sm {{ $projectBadgeClass }}">
                            {{ $displayStatus }}
                        </span>
                    </div>

                    <p style="font-size:13px;color:#64748B;margin-top:12px;line-height:1.55">
                        {{ str($project->description)->stripTags()->limit(130) }}
                    </p>

                    <div style="margin-top:16px">
                        <div class="spread" style="font-size:12px;color:#64748B;font-weight:600;margin-bottom:6px">
                            <span>Progress · {{ $doneTasks }}/{{ $totalTasks }} tasks</span>
                            <span class="tc-num">{{ $progress }}%</span>
                        </div>

                        <div class="progress">
                            <div class="progress-bar {{ $barClass }}" style="width:{{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:16px;padding-top:14px;border-top:1px solid #F1F5F9">
                        <div>
                            <div class="kicker">DEADLINE</div>
                            <div style="font-size:13px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                                {{ optional($project->deadline)->format('d M Y') ?: 'TBD' }}
                            </div>
                        </div>

                        <div>
                            <div class="kicker">START</div>
                            <div style="font-size:13px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                                {{ optional($project->start_date)->format('d M Y') ?: '-' }}
                            </div>
                        </div>

                        <div style="margin-left:auto">
                            <div class="kicker">TASKS</div>
                            <div style="font-size:13px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                                {{ $totalTasks }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-pad" style="text-align:center;color:#94A3B8;font-size:13.5px">
                    No projects found.
                </div>
            @endforelse
        </div>

        <div class="tc-pagination">
            {{ $projects->links() }}
        </div>

        <div class="card" style="margin-top:18px">
            <div class="spread" style="padding:16px 18px;border-bottom:1px solid #F1F5F9">
                <span class="section-title" style="margin-right:auto">My Tasks</span>
            </div>

            @forelse($tasks as $task)
                @php
                    $isDone = in_array($task->status, ['done', 'Done', 'completed', 'Completed']);

                    $taskStatus = strtolower(str_replace(' ', '_', $task->status));

                    $taskBadgeClass = match ($taskStatus) {
                        'done', 'completed' => 'badge-green',
                        'in_progress' => 'badge-blue',
                        'to_do', 'pending' => 'badge-amber',
                        default => 'badge-gray',
                    };
                @endphp

                <div class="task-row">
                    <div class="task-check {{ $isDone ? 'done' : '' }}">
                        @if ($isDone)
                            ✓
                        @endif
                    </div>

                    <div style="flex:1;min-width:0">
                        <div
                            style="font-size:14px;font-weight:700;color:{{ $isDone ? '#94A3B8' : '#1E293B' }};text-decoration:{{ $isDone ? 'line-through' : 'none' }}">
                            {{ $task->name }}
                        </div>

                        <div class="row flex-wrap" style="gap:14px;margin-top:6px">
                            <span style="font-size:12.5px;color:#64748B;font-weight:600">
                                {{ $task->project->name ?? '-' }}
                            </span>

                            <span style="font-size:12.5px;color:#475569;font-weight:700">
                                Due {{ optional($task->end_date)->format('d M Y') ?: '-' }}
                            </span>
                        </div>
                    </div>

                    @if ($task->priority)
                        <span class="badge xs badge-blue">
                            {{ ucfirst($task->priority) }}
                        </span>
                    @endif

                    <span class="badge sm {{ $taskBadgeClass }}">
                        {{ $isDone ? 'Done' : ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
            @empty
                <div style="padding:26px;text-align:center;color:#94A3B8;font-size:13.5px">
                    No assigned tasks found.
                </div>
            @endforelse
        </div>

    </div>
@endsection
