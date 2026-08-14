<div class="p-4 space-y-8">

    <!-- Pending Approvals -->
    <section>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm0 0c0 1.5-2 2-2 4h4c0-2-2-2.5-2-4zM12 19h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pending Approvals
        </h3>

        @if (isset($pendingUsers) && $pendingUsers->count() > 0)
            <!-- Responsive Grid Card Layout -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 auto-rows-fr">
                @foreach ($pendingUsers as $user)
                    <div
                        class="flex flex-col justify-between bg-stone-200 dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-xl/30 hover:shadow-2xl  transition-all duration-300 group">

                        <div class="p-5 flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-semibold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4
                                        class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ Str::title($user->name) }}
                                    </h4>
                                    <p class="text-sm text-stone-600 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>

                            <div class="text-sm text-stone-600 dark:text-gray-400 space-y-1">
                                <p><span class="font-medium">Role:</span> {{ strtoupper($user->role) }}
                                </p>
                                <p><span class="font-medium">Divisi:</span>
                                    {{ $user->division?->nama_divisi ? Strtoupper($user->division?->nama_divisi) : '-' }}
                                </p>
                                <p><span class="font-medium">Jabatan:</span>
                                    {{ $user->position?->nama_jabatan ? Strtoupper($user->position?->nama_jabatan) : '-' }}
                                </p>
                                <p><span class="font-medium">Kantor:</span>
                                    {{ $user->office?->nama_kantor ? Strtoupper($user->office?->nama_kantor) : '-' }}
                                </p>
                                <p><span class="font-medium">Terdaftar:</span>
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="border-t border-gray-100 dark:border-slate-700 p-4 flex gap-3 rounded-xl bg-blue-950 dark:bg-slate-900/50">
                            <form action="{{ route('admin.user-activity.approve', $user->id) }}" method="POST"
                                class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                            </form>

                            <form action="{{ route('admin.user-activity.reject', $user->id) }}" method="POST"
                                class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if (method_exists($pendingUsers, 'links'))
                <div class="mt-6">
                    {{ $pendingUsers->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada permintaan persetujuan yang menunggu.
                </p>
            </div>
        @endif
    </section>


    <!-- Approval History -->
    <section>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Approval History
        </h3>

        @if (isset($approvalHistory) && $approvalHistory->count() > 0)
            <div
                class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-xl hover:shadow-2xl">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
                        <tr>
                            @foreach (['Nama', 'Email', 'Status', 'Tanggal', 'Oleh'] as $header)
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach ($approvalHistory as $history)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ Str::title($history->name) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ $history->email }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if ($history->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                        @elseif ($history->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                        @else bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400 @endif">
                                        {{ ucfirst($history->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($history->updated_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $history->approved_by ?? 'System' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada riwayat persetujuan.
                </p>
            </div>
        @endif
    </section>

</div>
