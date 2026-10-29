@props(['attendance'])

@php
$details = $attendance->update_time_details;
@endphp

@if ($details)
<span class="inline-flex flex-wrap items-center gap-x-3">
    @foreach ($details as $detail)
    <span class="inline-flex items-center gap-1">
        <span
            class="font-medium {{ $detail['key'] === 'in' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{
            $detail['label'] }}</span>
        <span>{{ $detail['time'] ?? '-' }}</span>
    </span>
    @endforeach
</span>
@else
{{ \Illuminate\Support\Str::of($attendance->start_time)->substr(0, 5) }}
@if ($attendance->end_time)
- {{ \Illuminate\Support\Str::of($attendance->end_time)->substr(0, 5) }}
@endif
@endif