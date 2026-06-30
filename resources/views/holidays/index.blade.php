@extends('layouts.app')

@section('title', 'Holidays - Teamiy Connect')
@section('page', 'holidays')
@section('page_title', 'Holidays')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/holidays.css') }}">
@endpush

@section('content')


        {{-- NEXT HOLIDAY CARD --}}
        @if ($nextHoliday)
            <div class="holiday-card"
                style="padding:20px 22px;display:flex;align-items:center;gap:18px;margin-bottom:20px;flex-wrap:wrap">

                <div
                    style="width:54px;height:54px;border-radius:14px;background:#F47B26;color:#fff;display:flex;align-items:center;justify-content:center;flex:none">
                    📅
                </div>

                <div style="flex:1;min-width:0">
                    <div style="font-size:11.5px;font-weight:800;color:#D2691E;letter-spacing:.05em">
                        NEXT HOLIDAY
                    </div>

                    <div style="font-size:20px;font-weight:800;color:#7A3D12;margin-top:3px">
                        {{ $nextHoliday->event }}
                    </div>

                    <div style="font-size:13px;color:#A85C2A;margin-top:2px">
                        {{ $nextHoliday->event_date->format('l, d M Y') }}
                        · {{ $nextHoliday->is_public_holiday ? 'Public Holiday' : 'Company Holiday' }}
                    </div>
                </div>
            </div>
        @endif

        {{-- HOLIDAYS LIST --}}
        <div class="card">
            <div style="padding:16px 18px;font-size:15px;font-weight:800">
                Company Holidays · {{ now()->year }}
            </div>

            @forelse($holidays as $holiday)
                @php
                    $date = \Carbon\Carbon::parse($holiday->event_date);

                    $month = $date->format('M');
                    $day = $date->format('d');
                    $weekday = $date->format('l');

                    $monthAccent = [
                        'Jan' => '#2563EB',
                        'Feb' => '#E11D48',
                        'Mar' => '#16A34A',
                        'Apr' => '#9333EA',
                        'May' => '#94A3B8',
                        'Jun' => '#F47B26',
                        'Jul' => '#0891B2',
                        'Aug' => '#057DB0',
                        'Sep' => '#6B46C1',
                        'Oct' => '#EA580C',
                        'Nov' => '#0E7C66',
                        'Dec' => '#C0392B',
                    ];

                    $color = $monthAccent[$month] ?? '#057DB0';
                @endphp

                <div class="row" style="gap:16px;padding:14px 18px;border-top:1px solid #F1F5F9">

                    <div style="width:52px;text-align:center;flex:none">
                        <div class="tc-num" style="font-size:20px;font-weight:800;line-height:1;color:{{ $color }}">
                            {{ $day }}
                        </div>

                        <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase">
                            {{ $month }}
                        </div>
                    </div>

                    <div style="width:1px;height:34px;background:#EEF2F7;flex:none"></div>

                    <div style="flex:1;min-width:0">
                        <div style="font-size:14.5px;font-weight:700;color:#1E293B">
                            {{ $holiday->event }}
                        </div>

                        <div style="font-size:12.5px;color:#94A3B8">
                            {{ $weekday }}
                        </div>

                        @if ($holiday->note)
                            <div style="font-size:12.5px;color:#64748B;margin-top:3px">
                                {!! $holiday->note !!}
                            </div>
                        @endif
                    </div>

                    <span class="badge {{ $holiday->is_public_holiday ? 'badge-blue' : 'badge-violet' }}">
                        {{ $holiday->is_public_holiday ? 'Public' : 'Company' }}
                    </span>
                </div>
            @empty
                @include('partials.empty-state', ['message' => 'No holidays found.'])
            @endforelse
        </div>

        <div style="margin-top:18px">
            {{ $holidays->links() }}
        </div>

@endsection
