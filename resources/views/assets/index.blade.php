@extends('layouts.app')

@section('title', 'Assets - Teamiy Connect')
@section('page', 'assets')
@section('page_title', 'Assets')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/assets.css') }}">
@endpush

@section('content')
    <div class="wrap">
        <section class="hero">
            <div class="blob"></div>
            <div class="z">
                <div class="date">Assigned equipment</div>
                <div class="greet">My Assets</div>
                <div class="summary">Assets assigned to your employee record with warranty and condition details.</div>
            </div>
        </section>

        @if (session('status'))
            <div class="card card-pad" style="margin-top:18px;color:#0F766E;background:#ECFDF5;border-color:#A7F3D0">
                {{ session('status') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="card card-pad" style="margin-top:18px;color:#B91C1C;background:#FEF2F2;border-color:#FECACA">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="cards-grid auto-150" style="margin-top:18px">
            <div class="card asset-stat-card">
                <span>Assignment Records</span>
                <strong class="tc-num">{{ $assetStats['records'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Currently Assigned</span>
                <strong class="tc-num text-green">{{ $assetStats['currently_assigned'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Returned</span>
                <strong class="tc-num text-blue">{{ $assetStats['returned'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Return Requests</span>
                <strong class="tc-num text-orange">{{ $assetStats['return_pending'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Working Assets</span>
                <strong class="tc-num text-green">{{ $assetStats['working'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Under Warranty</span>
                <strong class="tc-num text-orange">{{ $assetStats['under_warranty'] ?? 0 }}</strong>
            </div>

            <div class="card asset-stat-card">
                <span>Repaired</span>
                <strong class="tc-num text-orange">{{ $assetStats['repaired'] ?? 0 }}</strong>
            </div>
        </div>

        <div class="spread flex-wrap" style="margin-top:22px">
            <span class="section-title">Assigned Assets</span>
            <span class="badge badge-blue">{{ $assignments->total() }} records</span>
        </div>

        <div class="cards-grid auto-300 asset-card-grid" style="margin-top:14px">
            @forelse ($assignments as $assignment)
                @php
                    $asset = $assignment->asset;
                    $image = ltrim((string) ($asset?->image ?? ''), '/');
                    $imageUrl = null;

                    if ($image !== '') {
                        $imageUrl = str_starts_with($image, 'http://') || str_starts_with($image, 'https://')
                            ? $image
                            : asset(str_starts_with($image, 'storage/') || str_starts_with($image, 'assets/') ? $image : 'storage/'.$image);
                    }

                    $isWorking = strtolower((string) ($asset?->is_working ?? '')) === 'yes';
                    $assignmentStatus = strtolower((string) $assignment->status);
                    $isReturned = $assignmentStatus === 'returned';
                    $normalizedAssignmentStatus = str_replace('_', ' ', $assignmentStatus);
                    $isReturnPending = str_contains($normalizedAssignmentStatus, 'return') && str_contains($normalizedAssignmentStatus, 'pending');
                    $displayStatus = $isReturnPending ? 'Return Pending' : ($assignment->status ?: 'assigned');
                    $hasWarranty = (bool) ($asset?->warranty_available ?? false);
                    $warrantyActive = $hasWarranty && $asset?->warranty_end_date && $asset->warranty_end_date->greaterThanOrEqualTo(now()->startOfDay());
                @endphp

                <article class="card asset-card">
                    <div class="asset-card-top">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $asset?->name ?? 'Asset' }}">
                        @else
                            <div class="asset-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                    <path d="M8 20h8M12 16v4"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="asset-title-block">
                            <h3>{{ $asset?->name ?? 'Unnamed Asset' }}</h3>
                            <p>{{ $asset?->type?->name ?? 'No type assigned' }}</p>
                        </div>

                        @include('partials.status-badge', ['slot' => $displayStatus])
                    </div>

                    <div class="asset-tags">
                        <span class="badge {{ $isReturned ? 'badge-gray' : ($isReturnPending ? 'badge-amber' : 'badge-green') }}">
                            {{ $isReturned ? 'Returned' : ($isReturnPending ? 'Awaiting Approval' : 'Assigned to Me') }}
                        </span>
                        <span class="badge {{ $isWorking ? 'badge-green' : 'badge-red' }}">
                            {{ $isWorking ? 'Working' : 'Not Working' }}
                        </span>
                        <span class="badge {{ (bool) ($asset?->is_available ?? false) ? 'badge-blue' : 'badge-gray' }}">
                            {{ (bool) ($asset?->is_available ?? false) ? 'Available' : 'Assigned' }}
                        </span>
                        <span class="badge {{ $warrantyActive ? 'badge-violet' : 'badge-gray' }}">
                            {{ $warrantyActive ? 'Warranty Active' : 'No Warranty' }}
                        </span>
                        @if ((bool) ($asset?->is_repaired ?? false))
                            <span class="badge badge-orange">Repaired</span>
                        @endif
                    </div>

                    <div class="asset-section-label">Asset Details</div>
                    <div class="asset-details">
                        <div>
                            <span>Asset Code</span>
                            <strong>{{ $asset?->asset_code ?: '-' }}</strong>
                        </div>
                        <div>
                            <span>Serial No.</span>
                            <strong>{{ $asset?->asset_serial_no ?: '-' }}</strong>
                        </div>
                        <div>
                            <span>Asset Type</span>
                            <strong>{{ $asset?->type?->name ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Asset Branch</span>
                            <strong>{{ $asset?->branch?->name ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Type Branch</span>
                            <strong>{{ $asset?->type?->branch?->name ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="asset-section-label">Assignment Details</div>
                    <div class="asset-details">
                        <div>
                            <span>Assigned Date</span>
                            <strong>{{ $assignment->assigned_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Returned Date</span>
                            <strong>{{ $assignment->returned_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Return Condition</span>
                            <strong>{{ $assignment->return_condition ?: '-' }}</strong>
                        </div>
                        <div>
                            <span>Assignment Branch</span>
                            <strong>{{ $assignment->branch?->name ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Department</span>
                            <strong>{{ $assignment->department?->dept_name ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Last Updated</span>
                            <strong>{{ $assignment->updated_at?->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="asset-section-label">Purchase & Warranty</div>
                    <div class="asset-details">
                        <div>
                            <span>Purchased Date</span>
                            <strong>{{ $asset?->purchased_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Warranty</span>
                            <strong>{{ $hasWarranty ? 'Available' : 'Not Available' }}</strong>
                        </div>
                        <div>
                            <span>Warranty End</span>
                            <strong>{{ $asset?->warranty_end_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                        <div>
                            <span>Repair Status</span>
                            <strong>{{ (bool) ($asset?->is_repaired ?? false) ? 'Repaired' : 'Not Repaired' }}</strong>
                        </div>
                        <div>
                            <span>Available</span>
                            <strong>{{ (bool) ($asset?->is_available ?? false) ? 'Yes' : 'No' }}</strong>
                        </div>
                        <div>
                            <span>Working</span>
                            <strong>{{ $asset?->is_working ?: '-' }}</strong>
                        </div>
                    </div>

                    @if ($assignment->notes)
                        <div class="asset-note">
                            <strong>Return Notes</strong>
                            <span>{{ $assignment->notes }}</span>
                        </div>
                    @endif

                    @if ($isReturnPending)
                        <div class="asset-return-waiting">
                            Return request submitted. Waiting for admin approval.
                        </div>
                    @elseif (! $isReturned)
                        <details class="asset-return-box">
                            <summary>
                                <span>Request Asset Return</span>
                            </summary>

                            <form method="POST" action="{{ route('assets.return-request', $assignment) }}">
                                @csrf

                                <label>
                                    Return Condition
                                    <select name="return_condition" required>
                                        <option value="working" @selected(old('return_condition', $assignment->return_condition) === 'working')>Working</option>
                                        <option value="non_working" @selected(old('return_condition', $assignment->return_condition) === 'non_working')>Non Working</option>
                                        <option value="maintenance" @selected(old('return_condition', $assignment->return_condition) === 'maintenance')>Maintenance</option>
                                    </select>
                                </label>

                                <label>
                                    Notes
                                    <textarea name="notes" rows="3" maxlength="500" placeholder="Add return notes for admin">{{ old('notes') }}</textarea>
                                </label>

                                <button type="submit" class="btn btn-primary btn-block">
                                    Send Return Request
                                </button>
                            </form>
                        </details>
                    @endif
                </article>
            @empty
                <div class="card card-pad asset-empty">
                    No assigned assets found.
                </div>
            @endforelse
        </div>

        @if ($assignments->hasPages())
            <div class="card-pad">{{ $assignments->links() }}</div>
        @endif

        
    </div>
@endsection
