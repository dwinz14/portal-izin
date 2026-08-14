<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Master Data Jenis Cuti') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola Data Jenis Cuti Perusahaan.
                </p>
            </div>
            <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex justify-between items-center">
                <a href="{{ route('admin.leave-types.create') }}"
                    class="inline-flex items-center px-3 py-2 bg-primary-600 border border-transparent rounded-full font-medium text-xs text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Jenis Cuti
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ showFilters: false, confirmDelete: false, selectedLeaveType: null, selectedLeaveTypeName: '', confirmToggle: false, selectedAction: '' }">
        @if (session('success'))
            <div
                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">


            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                #</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Jenis Cuti</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Kuota (Hari)</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Gender</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        @forelse ($leaveTypes as $index => $leaveType)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $leaveTypes->firstItem() + $index }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ strtoupper($leaveType->name) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    @if ($leaveType->quota > 0)
                                        {{ $leaveType->quota }} hari
                                    @else
                                        Tanpa Batas
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    @if ($leaveType->gender)
                                        {{ $leaveType->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    @else
                                        Semua
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <button
                                        @click="confirmToggle = true; selectedLeaveType = {{ $leaveType->id }}; selectedLeaveTypeName = '{{ $leaveType->name }}'; selectedAction = '{{ $leaveType->is_active ? 'nonaktifkan' : 'aktifkan' }}'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $leaveType->is_active ? 'bg-green-600' : 'bg-stone-500 dark:bg-gray-700' }}">
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $leaveType->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <span
                                        class="ml-2 text-xs font-medium {{ $leaveType->is_active ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                        {{ $leaveType->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.leave-types.edit', $leaveType->id) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button
                                            @click="confirmDelete = true; selectedLeaveType = {{ $leaveType->id }}; selectedLeaveTypeName = '{{ $leaveType->name }}'"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="mt-2">Tidak ada data jenis cuti ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaveTypes->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-200 dark:border-slate-700">
                    {{ $leaveTypes->links() }}
                </div>
            @endif
        </div>

        <!-- Modal delete leave type -->
        <div x-show="confirmDelete" x-transition x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md transform transition-all duration-300 ease-out"
                x-show="confirmDelete" x-transition:enter="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100" x-transition:leave="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0">
                <h2 class="text-lg font-semibold mb-4">Hapus Jenis Cuti</h2>
                <p class="mb-4">
                    Apakah Anda yakin ingin menghapus jenis cuti <span class="font-bold"
                        x-text="selectedLeaveTypeName"></span>?
                </p>
                <form
                    x-bind:action="'{{ route('admin.leave-types.destroy', ':id') }}'.replace(':id', selectedLeaveType)"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="confirmDelete = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal toggle leave type -->
        <div x-show="confirmToggle" x-transition x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md transform transition-all duration-300 ease-out"
                x-show="confirmToggle" x-transition:enter="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100" x-transition:leave="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0">
                <h2 class="text-lg font-semibold mb-4">Konfirmasi</h2>
                <p class="mb-4">
                    Apakah Anda yakin ingin <span class="font-bold" x-text="selectedAction"></span> jenis cuti <span
                        class="font-bold" x-text="selectedLeaveTypeName"></span>?
                </p>
                <form
                    x-bind:action="'{{ route('admin.leave-types.toggle', ':id') }}'.replace(':id', selectedLeaveType)"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="confirmToggle = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
