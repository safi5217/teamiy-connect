@extends('layouts.app')

@section('title', 'Payroll - Teamiy Connect')
@section('page', 'payroll')
@section('page_title', 'Payroll')

@section('content')
    <div class="wrap">
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">Salary and payroll</div>
                <div class="greet">My Payroll</div>
                <div class="summary">Generated payroll records, salary totals, and deductions.</div>
            </div>
        </section>

        <div class="cards-grid auto-150" style="margin-top:18px">
            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Payroll Records</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#1E293B" class="tc-num">
                    {{ $payrollStats['records'] ?? 0 }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Base Salary</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#1E293B" class="tc-num">
                    {{ number_format((float) ($payrollStats['base_salary'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Net Salary</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#16A34A" class="tc-num">
                    {{ number_format((float) ($payrollStats['net_salary'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Overtime Pay</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#057DB0" class="tc-num">
                    {{ number_format((float) ($payrollStats['overtime_pay'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">Deductions</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#B91C1C" class="tc-num">
                    {{ number_format((float) ($payrollStats['deductions'] ?? 0), 2) }}
                </div>
            </div>

            <div class="card" style="padding:16px">
                <div style="font-size:12.5px;color:#64748B;font-weight:700">TADA Amount</div>
                <div style="font-size:22px;font-weight:800;margin-top:6px;color:#B26A00" class="tc-num">
                    {{ number_format((float) ($payrollStats['tada_amount'] ?? 0), 2) }}
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:18px">
            <div class="spread flex-wrap" style="padding:16px 18px">
                <span class="section-title" style="margin-right:auto">Generated Payrolls</span>
                <span class="badge badge-blue">{{ $payrolls->total() }} records</span>
            </div>

            <div class="table-wrap">
                <table class="table" style="min-width:1180px">
                    <thead>
                        <tr>
                            <th>Range</th>
                            <th>Payroll Type</th>
                            <th>Payment Type</th>
                            <th>Worked</th>
                            <th>OT Hours</th>
                            <th>UT Hours</th>
                            <th>Unpaid Leave</th>
                            <th>Base</th>
                            <th>OT Pay</th>
                            <th>TADA</th>
                            <th>Deductions</th>
                            <th>Tax</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php
                                $deductions = (float) $payroll->undertime_deduction + (float) $payroll->unpaid_leave_deduction;
                            @endphp
                            <tr>
                                <td>{{ $payroll->range ?: '-' }}</td>
                                <td>{{ $payroll->payroll_type ?: '-' }}</td>
                                <td>{{ $payroll->payment_type ?: '-' }}</td>
                                <td>{{ $payroll->worked_hours ?? '-' }}</td>
                                <td>{{ $payroll->overtime_hours ?? '-' }}</td>
                                <td>{{ $payroll->undertime_hours ?? '-' }}</td>
                                <td>{{ $payroll->total_unpaid_leave_days ?? '-' }}</td>
                                <td>{{ number_format((float) $payroll->base_salary, 2) }}</td>
                                <td>{{ number_format((float) $payroll->overtime_pay, 2) }}</td>
                                <td>{{ number_format((float) $payroll->tada_amount, 2) }}</td>
                                <td>{{ number_format($deductions, 2) }}</td>
                                <td>{{ number_format((float) $payroll->tax, 2) }}</td>
                                <td style="font-weight:800;color:#1E293B">{{ number_format((float) $payroll->net_salary, 2) }}</td>
                                <td>@include('partials.status-badge', ['slot' => $payroll->status])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14">No generated payroll found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-pad">{{ $payrolls->links() }}</div>
        </div>
    </div>
@endsection
