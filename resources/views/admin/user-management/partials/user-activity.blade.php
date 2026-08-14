<div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Aktivitas User</h3>
        <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ $onlineCount }} user online
        </span>
    </div>
</div>

<div class="shadow-xl hover:shadow-2xl overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
            <tr>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    #</th>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Nama</th>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Role</th>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Divisi</th>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Login Terakhir</th>
                <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
            @forelse ($users as $index => $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $users->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                        {{ strtoupper($user->name) }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($user->role === 'super_admin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                            @elseif($user->role === 'hrd') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                            @elseif($user->role === 'kabag-pincab') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                            @elseif($user->role === 'kasie') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                            @elseif($user->role === 'staff') bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 @endif">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ strtoupper($user->division_name) ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($user->is_online) bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 @endif">
                            @if ($user->is_online)
                                Online
                            @else
                                Offline
                            @endif
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2">Tidak ada user yang tersedia.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700">
    {{ $users->links() }}
</div>
