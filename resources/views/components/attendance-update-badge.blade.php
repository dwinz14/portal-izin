@props(['attendance'])

@if ($attendance->type === \App\Models\AttendanceRequest::TYPE_UPDATE_ATTENDANCE && $attendance->update_type)
<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
    bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300']) }}>
    {{ $attendance->update_type_label }}
</span>
@endif