@props(['eventType' => 'other', 'color' => 'gray', 'size' => 'md'])

@php
    // SVG path data per event type (Heroicons outline)
    $paths = [
        'auth.login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
        'auth.logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'auth.password_reset' =>
            'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
        'auth.password_changed' =>
            'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        'auth.otp_verified' =>
            'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'leave.submitted' => 'M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z',
        'leave.cancelled' =>
            'M9 13h6m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'leave.revision_accepted' => 'M5 13l4 4L19 7',
        'leave.revision_rejected' => 'M6 18L18 6M6 6l12 12',
        'approval.leave_approved' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'approval.leave_rejected' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'approval.revision_requested' =>
            'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'approval.attendance_approved' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'approval.attendance_rejected' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'attendance.submitted' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'attendance.cancelled' => 'M6 18L18 6M6 6l12 12',
        'profile.updated' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'admin.user_approved' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        'admin.user_rejected' => 'M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6',
        'admin.quota_generated' =>
            'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    ];

    $bgText = [
        'blue' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400',
        'green' => 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400',
        'red' => 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400',
        'amber' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400',
        'indigo' => 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400',
        'purple' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400',
        'slate' => 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300',
        'gray' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300',
    ];

    $sizeClass = $size === 'lg' ? 'w-10 h-10' : ($size === 'sm' ? 'w-6 h-6' : 'w-8 h-8');
    $iconSize = $size === 'lg' ? 'w-5 h-5' : ($size === 'sm' ? 'w-3 h-3' : 'w-4 h-4');

    $d = $paths[$eventType] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    $colors = $bgText[$color] ?? $bgText['gray'];
@endphp

<div class="flex-shrink-0 {{ $sizeClass }} rounded-full {{ $colors }} flex items-center justify-center">
    <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d }}" />
    </svg>
</div>
