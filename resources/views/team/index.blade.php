@extends('layouts.app')

@section('title', 'Team Sheet - Teamiy Connect')
@section('page', 'team')
@section('page_title', 'Team Sheet')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/team.css') }}">

    <style>
        .team-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
        }

        .team-search {
            background: #fff;
            border: 1.5px solid #E7ECF3;
            width: min(300px, 100%);
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
        }

        .team-search input {
            border: 0;
            outline: 0;
            width: 100%;
            color: #334155;
            font-size: 13px;
            background: transparent;
        }

        .team-card {
            cursor: pointer;
            min-height: 148px;
            border-radius: 14px;
            width: 100%;
            text-align: left;
            font: inherit;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .team-card:hover {
            transform: translateY(-2px);
            border-color: #D7E2EF;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
        }

        .team-avatar {
            width: 46px;
            height: 46px;
            border-radius: 11px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 900;
            flex: none;
        }

        .team-avatar-lg {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            font-size: 26px;
            border: 4px solid #fff;
            box-shadow: 0 6px 16px -8px rgba(0, 0, 0, .3);
        }

        .team-c0 { background: #3498DB; }
        .team-c1 { background: #805AD5; }
        .team-c2 { background: #E28400; }
        .team-c3 { background: #319C7A; }
        .team-c4 { background: #C65378; }
        .team-c5 { background: #456FC5; }

        .team-pill {
            font-size: 11.5px;
            font-weight: 800;
            color: #475569;
            background: #F1F5F9;
            padding: 4px 9px;
            border-radius: 7px;
        }

        .team-pill.active {
            color: #16803A;
            background: #E7F7EC;
        }

        .team-pill.inactive {
            color: #A84137;
            background: #FDEDED;
        }

        #teamMemberModalRoot {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        #teamMemberModalRoot .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .team-member-modal {
            width: min(440px, 100%);
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(15, 23, 42, .32);
        }

        .team-member-modal-head {
            height: 74px;
            background: linear-gradient(120deg, #044d6e, #057DB0 60%, #16A6DE);
            position: relative;
        }

        .team-modal-x {
            position: absolute;
            top: 14px;
            right: 14px;
            color: #fff;
            opacity: .9;
            border: 0;
            background: transparent;
            cursor: pointer;
            padding: 4px;
        }

        .team-detail-list {
            text-align: left;
            margin-top: 20px;
            display: grid;
            gap: 1px;
            background: var(--line-2);
            border: 1px solid var(--line-2);
            border-radius: 12px;
            overflow: hidden;
        }

        .team-detail-row {
            background: #fff;
            padding: 12px 16px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            align-items: center;
        }

        .team-detail-row span:first-child {
            font-size: 12.5px;
            color: #94A3B8;
            font-weight: 800;
        }

        .team-detail-row span:last-child {
            min-width: 0;
            text-align: right;
            color: #334155;
            font-size: 12.5px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@section('content')
    @php
        $memberPayload = $members->getCollection()->values()->map(function ($member, int $index): array {
            $parts = collect(explode(' ', (string) $member->name))->filter();
            $initials = $parts->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('') ?: 'TM';
            $role = $member->post->post_name ?? 'Employee';
            $department = $member->department->dept_name ?? 'General';
            $email = $member->work_email ?: $member->email;

            return [
                'id' => $member->id,
                'name' => $member->name ?? 'Team member',
                'initials' => $initials,
                'color' => 'team-c'.($index % 6),
                'role' => $role,
                'department' => $department,
                'branch' => $member->branch->name ?? '-',
                'email' => $email ?: '-',
                'personalEmail' => $member->email ?: '-',
                'phone' => $member->phone ?: '-',
                'manager' => $member->supervisor->name ?? 'No supervisor assigned',
                'joined' => optional($member->joining_date)->format('d M Y') ?: '-',
                'employeeCode' => $member->employee_code ?: '-',
                'employmentType' => ucfirst((string) ($member->employment_type ?: '-')),
                'userType' => ucfirst((string) ($member->user_type ?: '-')),
                'status' => $member->is_active ? 'Active' : 'Inactive',
                'statusClass' => $member->is_active ? 'active' : 'inactive',
                'gender' => ucfirst((string) ($member->gender ?: '-')),
            ];
        });
    @endphp

    <div class="wrap">
        <div class="team-toolbar">
            <div class="team-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="M21 21l-4-4"></path>
                </svg>
                <input id="teamSearch" placeholder="Search by name, department..." autocomplete="off">
            </div>
            
            <span style="font-size:13px;color:#94A3B8;font-weight:700" id="teamMemberCount">
                {{ $members->total() }} members
            </span>
        </div>

        <div class="cards-grid auto-258" id="teamGrid">
            @forelse ($memberPayload as $member)
                <button class="card card-pad team-card" type="button" data-team-card
                    data-member-id="{{ $member['id'] }}"
                    data-search="{{ strtolower($member['name'].' '.$member['role'].' '.$member['department'].' '.$member['email']) }}">
                    <div class="row" style="gap:12px;align-items:flex-start">
                        <div class="team-avatar {{ $member['color'] }}">{{ $member['initials'] }}</div>
                        <div style="min-width:0;flex:1;text-align:left">
                            <div style="font-size:14.5px;font-weight:900;color:#0F172A;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $member['name'] }}
                            </div>
                            <div style="font-size:12.5px;color:#64748B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $member['role'] }}
                            </div>
                        </div>
                    </div>

                    <div class="row flex-wrap" style="gap:8px;margin-top:14px">
                        <span class="team-pill">{{ $member['department'] }}</span>
                        <span class="team-pill {{ $member['statusClass'] }}">{{ $member['status'] }}</span>
                    </div>

                    <div class="row" style="gap:7px;margin-top:12px;font-size:12px;color:#94A3B8;min-width:0">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" style="flex:none">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="M3 7l9 6 9-6"></path>
                        </svg>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $member['email'] }}</span>
                    </div>
                </button>
            @empty
                @include('partials.empty-state', ['message' => 'No team members found.'])
            @endforelse
        </div>

        <div style="margin-top:18px">{{ $members->links() }}</div>
    </div>

    <div id="teamMemberModalRoot" style="display:none">
        <div class="overlay" id="teamMemberModalOverlay">
            <div class="team-member-modal">
                <div class="team-member-modal-head">
                    <button class="team-modal-x" type="button" id="closeTeamMemberModal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div style="padding:0 24px 24px;margin-top:-34px;text-align:center">
                    <div style="display:flex;justify-content:center">
                        <div class="team-avatar team-avatar-lg" id="teamModalAvatar">TM</div>
                    </div>

                    <div style="font-size:19px;font-weight:900;margin-top:12px;color:#0F172A" id="teamModalName"></div>
                    <div style="font-size:13.5px;color:#64748B" id="teamModalRole"></div>

                    <div class="row" style="justify-content:center;gap:8px;margin-top:10px">
                        <span class="team-pill" id="teamModalDepartment"></span>
                        <span class="team-pill active" id="teamModalStatus"></span>
                    </div>

                    <div class="team-detail-list" id="teamModalDetails"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.TEAMIY_TEAM_MEMBERS = @json($memberPayload);
    </script>
    <script src="{{ asset('js/team.js') }}"></script>
@endpush
