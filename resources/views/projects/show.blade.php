@extends('layouts.app')

@section('title', $project->name . ' - Teamiy Connect')
@section('page', 'projects')
@section('page_title', 'Project Detail')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endpush

@section('content')
    <div class="wrap">

        <a href="{{ route('projects.index') }}" class="back-btn" style="text-decoration:none">
            ← All projects
        </a>

        @php
            $status = strtolower(str_replace(' ', '_', $project->status));
            $barClass = $status === 'completed' || $status === 'done' ? 'green' : ($status === 'on_hold' ? 'amber' : '');

            $projectBadgeClass = match ($status) {
                'completed', 'done' => 'badge-green',
                'in_progress', 'active' => 'badge-blue',
                'on_hold', 'pending', 'to_do' => 'badge-amber',
                default => 'badge-gray',
            };
        @endphp

        <div class="card" style="padding:24px;margin-bottom:18px">
            <div class="spread flex-wrap" style="align-items:flex-start">
                <div style="min-width:0;flex:1">
                    <div class="row flex-wrap" style="gap:10px">
                        <h2 style="font-size:22px;font-weight:800;letter-spacing:-.02em">
                            {{ $project->name }}
                        </h2>

                        <span class="badge sm {{ $projectBadgeClass }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>

                        @if ($project->priority ?? false)
                            <span class="badge sm badge-blue">
                                {{ ucfirst($project->priority) }}
                            </span>
                        @endif
                    </div>

                    <p style="font-size:13.5px;color:#64748B;margin-top:8px;line-height:1.55;max-width:620px">
                        {{ str($project->description)->stripTags() ?: 'No description yet.' }}
                    </p>
                </div>
            </div>

            <div class="row flex-wrap" style="gap:28px;margin-top:20px;padding-top:18px;border-top:1px solid #F1F5F9">
                <div style="flex:1;min-width:180px">
                    <div class="spread" style="font-size:12px;color:#64748B;font-weight:600;margin-bottom:6px">
                        <span>Overall progress</span>
                        <span class="tc-num">{{ $progress }}%</span>
                    </div>

                    <div class="progress lg">
                        <div class="progress-bar {{ $barClass }}" style="width:{{ $progress }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="kicker">DEADLINE</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                        {{ optional($project->deadline)->format('d M Y') ?: 'TBD' }}
                    </div>
                </div>

                <div>
                    <div class="kicker">START DATE</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                        {{ optional($project->start_date)->format('d M Y') ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="kicker">TOTAL TASKS</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;margin-top:2px" class="tc-num">
                        {{ $totalTasks }}
                    </div>
                </div>
            </div>
        </div>

        <div class="cols-3" style="margin-bottom:18px">
            <div class="card" style="padding:15px 18px">
                <div style="font-size:12px;color:#64748B;font-weight:700">To Do</div>
                <div style="font-size:23px;font-weight:800;margin-top:5px;color:#5B6878" class="tc-num">
                    {{ $todoTasks }}
                </div>
            </div>

            <div class="card" style="padding:15px 18px">
                <div style="font-size:12px;color:#64748B;font-weight:700">In Progress</div>
                <div style="font-size:23px;font-weight:800;margin-top:5px;color:#1763B6" class="tc-num">
                    {{ $progressTasks }}
                </div>
            </div>

            <div class="card" style="padding:15px 18px">
                <div style="font-size:12px;color:#64748B;font-weight:700">Done</div>
                <div style="font-size:23px;font-weight:800;margin-top:5px;color:#1A7F44" class="tc-num">
                    {{ $doneTasks }}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="spread" style="padding:16px 18px;border-bottom:1px solid #F1F5F9">
                <span class="section-title" style="margin-right:auto">Tasks</span>

                <select id="taskFilter"
                        class="select"
                        onchange="filterTasks()"
                        style="width:auto;padding:8px 11px;font-size:12.5px">
                    <option value="all">All</option>
                    <option value="to_do">To Do</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
            </div>

            @forelse($project->tasks as $task)
                @php
                    $isDone = in_array($task->status, ['done', 'Done', 'completed', 'Completed']);
                    $isOverdue = !$isDone && $task->end_date && $task->end_date->isPast();

                    $taskStatus = strtolower(str_replace(' ', '_', $task->status));

                    if (in_array($taskStatus, ['done', 'completed'])) {
                        $filterStatus = 'done';
                    } elseif ($taskStatus === 'in_progress') {
                        $filterStatus = 'in_progress';
                    } else {
                        $filterStatus = 'to_do';
                    }

                    $taskBadgeClass = match ($taskStatus) {
                        'done', 'completed' => 'badge-green',
                        'in_progress' => 'badge-blue',
                        'to_do', 'pending' => 'badge-amber',
                        default => 'badge-gray',
                    };
                @endphp

                <div class="task-row"
                     data-task-id="{{ $task->id }}"
                     data-status="{{ $filterStatus }}"
                     onclick="toggleTaskDone(this)"
                     style="cursor:pointer">

                    <div class="task-check {{ $isDone ? 'done' : '' }}">
                        @if ($isDone)
                            ✓
                        @endif
                    </div>

                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:{{ $isDone ? '#94A3B8' : '#1E293B' }};text-decoration:{{ $isDone ? 'line-through' : 'none' }}">
                            {{ $task->name }}
                        </div>

                        <div class="row flex-wrap" style="gap:14px;margin-top:6px">
                            <span style="font-size:12.5px;color:{{ $isOverdue ? '#C0392B' : '#475569' }};font-weight:700">
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
                    No tasks yet.
                </div>
            @endforelse
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function filterTasks() {
            const selected = document.getElementById('taskFilter').value;
            const rows = document.querySelectorAll('.task-row');

            rows.forEach(row => {
                const status = row.dataset.status;

                row.style.display = selected === 'all' || status === selected
                    ? 'flex'
                    : 'none';
            });
        }

        function toggleTaskDone(row) {
            const taskId = row.dataset.taskId;

            fetch(`/tasks/${taskId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.status) return;

                location.reload();
            })
            .catch(() => {
                alert('Task status update failed.');
            });
        }
    </script>
@endpush