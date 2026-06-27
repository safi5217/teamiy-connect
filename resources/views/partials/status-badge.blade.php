@php
    $value = strtolower((string) ($slot ?? ''));
    $class = match (true) {
        str_contains($value, 'approved'), str_contains($value, 'accepted'), str_contains($value, 'completed'), str_contains($value, 'assigned') => 'badge-green',
        str_contains($value, 'pending'), str_contains($value, 'review'), str_contains($value, 'progress'), str_contains($value, 'started') => 'badge-amber',
        str_contains($value, 'reject'), str_contains($value, 'block'), str_contains($value, 'term') => 'badge-red',
        default => 'badge-gray',
    };
@endphp

<span class="badge {{ $class }}">{{ $slot }}</span>
