<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Manajemen Kuota Cuti
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola dan pantau sisa kuota cuti seluruh karyawan — {{ now()->year }}
                </p>
            </div>
            <form method="GET" action="{{ route('hrd.quota.index') }}" class="flex items-center gap-2">
                @foreach (request()->except('leave_type_id') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label class="text-sm font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Jenis
                    Cuti:</label>
                <select name="leave_type_id" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500 pr-8">
                    @foreach ($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" {{ $leaveTypeId == $leaveType->id ? 'selected' : '' }}>
                            {{ $leaveType->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="space-y-5" x-data="quotaManager()" x-init="init()">

        {{-- ================================================================
             MODAL: KONFIRMASI AKSI BERBAHAYA
        ================================================================ --}}
        <div x-show="confirmDialog.show" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmDialog.show = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 w-full"
                    :class="{
                        'bg-red-500': confirmDialog.type === 'danger',
                        'bg-amber-500': confirmDialog.type === 'warning',
                        'bg-blue-500': confirmDialog.type === 'info'
                    }">
                </div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center"
                            :class="{
                                'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400': confirmDialog
                                    .type === 'danger',
                                'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400': confirmDialog
                                    .type === 'warning',
                                'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400': confirmDialog
                                    .type === 'info'
                            }">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100"
                                x-text="confirmDialog.title"></h3>
                            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 leading-relaxed"
                                x-text="confirmDialog.message"></p>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="confirmDialog.show = false"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="confirmAction()"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors"
                            :class="{
                                'bg-red-600 hover:bg-red-700': confirmDialog.type === 'danger',
                                'bg-amber-500 hover:bg-amber-600': confirmDialog.type === 'warning',
                                'bg-blue-600 hover:bg-blue-700': confirmDialog.type === 'info'
                            }">
                            Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             MODAL: EDIT KUOTA INDIVIDUAL
        ================================================================ --}}
        <div x-show="editModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeEditModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1 bg-primary-600 w-full"></div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Ubah Sisa Kuota</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="editModal.name"></p>
                        </div>
                        <button @click="closeEditModal()"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-5 text-center">
                        <div class="bg-gray-50 dark:bg-slate-700/60 rounded-xl p-3">
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-100" x-text="editModal.total">
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total</div>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-3">
                            <div class="text-xl font-bold text-orange-600 dark:text-orange-400"
                                x-text="editModal.used"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Terpakai</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-3">
                            <div class="text-xl font-bold text-green-600 dark:text-green-400"
                                x-text="editModal.remaining"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sisa saat ini</div>
                        </div>
                    </div>
                    <form :action="editModal.action" method="POST" @submit="editModal.show = false">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Sisa Kuota Baru <span class="text-gray-400 font-normal">(hari)</span>
                            </label>
                            <input type="number" name="remaining" x-model="editModal.newRemaining" min="0"
                                max="365"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-center text-2xl font-bold focus:border-primary-500 focus:ring-primary-500 py-3"
                                placeholder="0" />
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                                Total kuota baru: <strong class="text-gray-600 dark:text-gray-300"
                                    x-text="parseInt(editModal.used) + parseInt(editModal.newRemaining || 0)"></strong>
                                hari
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="closeEditModal()"
                                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition-colors">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================================================================
             MODAL: BULK UPDATE
        ================================================================ --}}
        <div x-show="bulkModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="bulkModal.show = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1 bg-primary-600 w-full"></div>
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Ubah Kuota Massal</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                <span class="font-semibold text-primary-600 dark:text-primary-400"
                                    x-text="selectedIds.length"></span>
                                karyawan dipilih
                            </p>
                        </div>
                        <button @click="bulkModal.show = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Chip daftar nama yang dipilih --}}
                    <div class="mb-5 max-h-28 overflow-y-auto">
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="id in selectedIds" :key="id">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-medium">
                                    <span x-text="allRowData[id]?.name ?? rowData[id]?.name ?? id"></span>
                                    <button type="button" @click="toggleSelect(id)"
                                        class="text-primary-400 hover:text-primary-600">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <form action="{{ route('hrd.quota.bulkUpdate') }}" method="POST"
                        @submit.prevent="submitBulk($event)">
                        @csrf
                        <input type="hidden" name="leave_type_id" value="{{ $leaveTypeId }}">

                        {{-- Hidden user_ids — diisi oleh Alpine saat submit --}}
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="user_ids[]" :value="id">
                        </template>

                        {{-- Mode selector --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis
                                Perubahan</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="mode" value="set" x-model="bulkModal.mode"
                                        class="sr-only peer">
                                    <div
                                        class="flex flex-col items-center gap-1 px-3 py-2.5 rounded-xl border-2 text-center transition-all
                                        border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400
                                        peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/30 peer-checked:text-primary-700 dark:peer-checked:text-primary-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                        </svg>
                                        <span class="text-xs font-semibold">Set Nilai</span>
                                        <span class="text-[10px] opacity-70">Ganti ke angka ini</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="mode" value="add" x-model="bulkModal.mode"
                                        class="sr-only peer">
                                    <div
                                        class="flex flex-col items-center gap-1 px-3 py-2.5 rounded-xl border-2 text-center transition-all
                                        border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400
                                        peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/30 peer-checked:text-green-700 dark:peer-checked:text-green-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-xs font-semibold">Tambah</span>
                                        <span class="text-[10px] opacity-70">+N dari sisa</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="mode" value="subtract" x-model="bulkModal.mode"
                                        class="sr-only peer">
                                    <div
                                        class="flex flex-col items-center gap-1 px-3 py-2.5 rounded-xl border-2 text-center transition-all
                                        border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400
                                        peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/30 peer-checked:text-amber-700 dark:peer-checked:text-amber-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                        </svg>
                                        <span class="text-xs font-semibold">Kurangi</span>
                                        <span class="text-[10px] opacity-70">−N dari sisa</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Input nilai --}}
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Jumlah <span class="text-gray-400 font-normal">(hari)</span>
                            </label>
                            <input type="number" name="value" x-model="bulkModal.value" min="0"
                                max="365" required placeholder="0"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-center text-3xl font-bold focus:border-primary-500 focus:ring-primary-500 py-3" />

                            {{-- Preview ringkas --}}
                            <div class="mt-2 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-xl text-xs text-gray-500 dark:text-gray-400"
                                x-show="bulkModal.value > 0">
                                <template x-if="bulkModal.mode === 'set'">
                                    <span>Sisa kuota semua karyawan yang dipilih akan menjadi <strong
                                            class="text-gray-800 dark:text-gray-200"
                                            x-text="bulkModal.value"></strong> hari</span>
                                </template>
                                <template x-if="bulkModal.mode === 'add'">
                                    <span>Sisa kuota setiap karyawan yang dipilih akan <strong
                                            class="text-green-600">bertambah</strong> <strong
                                            class="text-gray-800 dark:text-gray-200"
                                            x-text="bulkModal.value"></strong> hari dari nilai saat ini</span>
                                </template>
                                <template x-if="bulkModal.mode === 'subtract'">
                                    <span>Sisa kuota setiap karyawan yang dipilih akan <strong
                                            class="text-amber-600">berkurang</strong> <strong
                                            class="text-gray-800 dark:text-gray-200"
                                            x-text="bulkModal.value"></strong> hari dari nilai saat ini (minimal
                                        0)</span>
                                </template>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="bulkModal.show = false"
                                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition-colors"
                                :class="{
                                    'bg-primary-600 hover:bg-primary-700': bulkModal.mode === 'set',
                                    'bg-green-600 hover:bg-green-700': bulkModal.mode === 'add',
                                    'bg-amber-500 hover:bg-amber-600': bulkModal.mode === 'subtract'
                                }">
                                Terapkan ke <span x-text="selectedIds.length"></span> Karyawan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================================================================
             STAT CARDS
        ================================================================ --}}
        @php
            $totalKaryawan = $userLeaveBalances->total();
            $kurangDari5 = $userLeaveBalances->filter(fn($b) => $b->remaining < 5)->count();
            $habis = $userLeaveBalances->filter(fn($b) => $b->remaining == 0)->count();
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-4 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalKaryawan }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Karyawan ditampilkan</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-4 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $effectiveQuota }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Hari efektif {{ now()->year }}</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-4 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $kurangDari5 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Kuota &lt; 5 hari</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-4 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $habis }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Kuota habis</div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             LAYOUT UTAMA
        ================================================================ --}}
        <div class="flex flex-col xl:flex-row gap-5 items-start">

            {{-- ── Kolom Kiri ── --}}
            <div class="flex-1 min-w-0 space-y-4">

                {{-- FILTER --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4">
                    <form method="GET" action="{{ route('hrd.quota.index') }}">
                        <input type="hidden" name="leave_type_id" value="{{ $leaveTypeId }}">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cari
                                    Nama</label>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        placeholder="Nama karyawan..."
                                        class="w-full pl-8 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                                <select name="position_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Semua Jabatan</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}"
                                            {{ $positionId == $position->id ? 'selected' : '' }}>
                                            {{ strtoupper($position->nama_jabatan) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kantor</label>
                                <select name="office_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Semua Kantor</option>
                                    @foreach ($offices as $office)
                                        <option value="{{ $office->id }}"
                                            {{ $officeId == $office->id ? 'selected' : '' }}>
                                            {{ strtoupper($office->nama_kantor) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Role</label>
                                <select name="role"
                                    class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Semua Role</option>
                                    @foreach (['staff' => 'Staff', 'kasie' => 'Kasie', 'kabag-pincab' => 'Kabag/Pincab', 'hrd' => 'HRD', 'direksi' => 'Direksi'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ $role == $val ? 'selected' : '' }}>
                                            {{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 justify-end">
                            <a href="{{ route('hrd.quota.index', ['leave_type_id' => $leaveTypeId]) }}"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-slate-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Reset
                            </a>
                            <button type="submit"
                                class="px-4 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold transition-colors">
                                Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TABEL KUOTA --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">

                    {{-- Toolbar tabel --}}
                    <div
                        class="px-5 py-3.5 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3">
                            {{-- Select all checkbox --}}
                            <label class="flex items-center gap-2 cursor-pointer group" title="Pilih semua">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only peer" x-ref="checkboxAll"
                                        @change="toggleSelectAll($event.target.checked)">
                                    <div
                                        class="w-5 h-5 rounded-md border-2 border-gray-300 dark:border-slate-500
                                        peer-checked:bg-primary-600 peer-checked:border-primary-600
                                        group-hover:border-primary-400 dark:group-hover:border-primary-500
                                        flex items-center justify-center transition-all">
                                        <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    {{-- Intermediate state (sebagian) --}}
                                    <div class="absolute inset-0 w-5 h-5 rounded-md flex items-center justify-center pointer-events-none"
                                        x-show="selectedIds.length > 0 && selectedIds.length < totalRows">
                                        <div class="w-2.5 h-0.5 bg-primary-600 rounded"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 select-none">Pilih semua</span>
                            </label>

                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Data Kuota
                                Karyawan</span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                                {{ $userLeaveBalances->total() }} karyawan
                            </span>
                        </div>

                        {{-- Keterangan atau hint --}}
                        <span class="text-xs text-gray-400 dark:text-gray-500 italic hidden sm:inline">
                            Centang karyawan untuk aksi massal
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr
                                    class="bg-slate-50 dark:bg-slate-700/50 border-b border-gray-200 dark:border-slate-600">
                                    <th class="w-10 px-4 py-3"></th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Karyawan</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                        Jabatan / Kantor</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Alokasi</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[160px]">
                                        Pemakaian</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Sisa</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ubah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @forelse ($userLeaveBalances as $balance)
                                    @php
                                        $pct =
                                            $balance->total_quota > 0
                                                ? round(($balance->used / $balance->total_quota) * 100)
                                                : 0;
                                        $barColor =
                                            $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-green-500');
                                        $sisaColor =
                                            $balance->remaining == 0
                                                ? 'text-red-600 dark:text-red-400'
                                                : ($balance->remaining < 5
                                                    ? 'text-amber-600 dark:text-amber-400'
                                                    : 'text-green-600 dark:text-green-400');
                                        $avatarColors = [
                                            'from-blue-500 to-blue-600',
                                            'from-violet-500 to-violet-600',
                                            'from-emerald-500 to-emerald-600',
                                            'from-rose-500 to-rose-600',
                                            'from-amber-500 to-amber-600',
                                            'from-cyan-500 to-cyan-600',
                                        ];
                                        $avatarColor =
                                            $avatarColors[crc32($balance->user->name) % count($avatarColors)];
                                        $actionUrl = route('hrd.quota.update', [$balance->user, $balance->leaveType]);
                                        $userId = $balance->user->id;
                                        $userName = addslashes(Str::title($balance->user->name));
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-700/40 transition-colors group"
                                        :class="selectedIds.includes({{ $userId }}) ?
                                            'bg-primary-50/60 dark:bg-primary-900/20' : ''">

                                        {{-- Checkbox --}}
                                        <td class="w-10 px-4 py-3">
                                            <label class="flex items-center justify-center cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" class="sr-only peer"
                                                        :checked="selectedIds.includes({{ $userId }})"
                                                        @change="toggleSelect({{ $userId }})">
                                                    <div
                                                        class="w-4.5 h-4.5 w-[18px] h-[18px] rounded border-2 border-gray-300 dark:border-slate-500
                                                        peer-checked:bg-primary-600 peer-checked:border-primary-600
                                                        flex items-center justify-center transition-all">
                                                        <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>

                                        {{-- Nama --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div>
                                                    <div
                                                        class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                                                        {{ Str::title($balance->user->name) }}</div>
                                                    <div class="flex items-center gap-1.5 mt-0.5">
                                                        <span
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                                            {{ strtoupper($balance->user->role) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Jabatan/Kantor --}}
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <div
                                                class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-tight">
                                                {{ strtoupper($balance->user->position->nama_jabatan ?? '-') }}</div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                {{ strtoupper($balance->user->office->nama_kantor ?? '-') }}</div>
                                        </td>

                                        {{-- Alokasi --}}
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="text-base font-bold text-gray-700 dark:text-gray-200">{{ $balance->total_quota }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block">hari</span>
                                        </td>

                                        {{-- Progress --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex-1 bg-gray-200 dark:bg-slate-600 rounded-full h-2 overflow-hidden min-w-[80px]">
                                                    <div class="h-2 rounded-full {{ $barColor }}"
                                                        style="width: {{ $pct }}%"></div>
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500 dark:text-gray-400 w-16 text-right whitespace-nowrap">
                                                    <span
                                                        class="font-medium text-orange-600 dark:text-orange-400">{{ $balance->used }}</span>
                                                    <span class="text-gray-400"> / {{ $balance->total_quota }}</span>
                                                </div>
                                            </div>
                                            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                                {{ $pct }}% terpakai</div>
                                        </td>

                                        {{-- Sisa --}}
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="text-base font-bold {{ $sisaColor }}">{{ $balance->remaining }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block">hari</span>
                                        </td>

                                        {{-- Tombol edit satuan --}}
                                        <td class="px-4 py-3 text-center">
                                            <button type="button"
                                                @click="openEditModal({
                                                    name: '{{ $userName }}',
                                                    total: {{ $balance->total_quota }},
                                                    used: {{ $balance->used }},
                                                    remaining: {{ $balance->remaining }},
                                                    action: '{{ $actionUrl }}'
                                                })"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all opacity-60 group-hover:opacity-100"
                                                title="Ubah kuota">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div
                                                    class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Tidak ada data kuota</p>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba ubah
                                                        filter atau generate kuota tahunan terlebih dahulu</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($userLeaveBalances->hasPages())
                        <div
                            class="px-5 py-3 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/40">
                            {{ $userLeaveBalances->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Kolom Kanan: Panel Admin ── --}}
            <div class="w-full xl:w-80 flex-shrink-0 space-y-4">

                {{-- GENERATE --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-4 py-3.5 border-b border-gray-200 dark:border-slate-700 flex items-center gap-2.5">
                        <div
                            class="w-7 h-7 rounded-lg bg-green-100 dark:bg-green-900/40 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Generate Kuota Tahunan
                            </h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Buat/update kuota semua karyawan
                            </p>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <div
                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3 text-xs text-green-700 dark:text-green-300">
                            <div class="font-semibold mb-1">Kuota tahun {{ now()->year }}</div>
                            <div class="flex justify-between"><span>Kuota dasar</span><span
                                    class="font-medium">{{ $defaultQuota }} hari</span></div>
                            <div class="flex justify-between"><span>Cuti bersama</span><span
                                    class="font-medium text-red-600 dark:text-red-400">− {{ $jointLeaveCount }}
                                    hari</span></div>
                            <div
                                class="border-t border-green-300 dark:border-green-700 mt-1.5 pt-1.5 flex justify-between font-semibold">
                                <span>Efektif</span><span>{{ $effectiveQuota }} hari</span>
                            </div>
                            <div class="mt-2 text-[10px] text-green-600 dark:text-green-400">
                                Pastikan <a href="{{ route('hrd.holidays.index') }}"
                                    class="underline font-medium">data hari libur</a> sudah lengkap sebelum generate.
                            </div>
                        </div>
                        <form action="{{ route('hrd.quota.generateAnnual') }}" method="POST"
                            @submit.prevent="showConfirmation($event, {
                                title: 'Generate Kuota Tahunan',
                                message: 'Proses ini akan membuat atau memperbarui kuota cuti untuk semua karyawan aktif. Lanjutkan?',
                                type: 'info'
                            })">
                            @csrf
                            <div class="mb-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun</label>
                                <input type="number" name="year" value="{{ now()->year }}" min="2020"
                                    max="{{ now()->year + 1 }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-green-500 focus:ring-green-500">
                            </div>
                            <button type="submit"
                                class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Generate Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                {{-- PENGATURAN --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-4 py-3.5 border-b border-gray-200 dark:border-slate-700 flex items-center gap-2.5">
                        <div
                            class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pengaturan Sistem</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Konfigurasi default kuota</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('hrd.quota.settings') }}" method="POST" class="space-y-3">
                            @csrf
                            <div
                                class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                                <div>
                                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300">Auto-buat kuota
                                        user baru</div>
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500">Otomatis saat karyawan
                                        ditambah</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="auto_generate_leave_balances" value="1"
                                        {{ $autoGenerate ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-checked:bg-primary-600 rounded-full transition-colors
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                    </div>
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kuota
                                    Default (hari)</label>
                                <input type="number" name="default_annual_leave_quota"
                                    value="{{ $effectiveQuota }}" min="0"
                                    class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                                Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================================================================
             FLOATING BULK ACTION BAR
             Muncul di bawah layar saat ada karyawan yang dicentang
        ================================================================ --}}
        <div x-show="selectedIds.length > 0" x-cloak class="fixed bottom-6 inset-x-0 flex justify-center z-40 px-4"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">

            <div
                class="bg-gray-900 dark:bg-slate-700 text-white rounded-2xl shadow-2xl shadow-gray-900/40 px-5 py-3.5 flex items-center gap-4 max-w-xl w-full">

                {{-- Info jumlah dipilih --}}
                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <div class="w-8 h-8 rounded-xl bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold leading-tight">
                            <span x-text="selectedIds.length"></span> dipilih
                        </div>
                        <div class="text-[10px] text-gray-400 leading-tight">karyawan</div>
                    </div>
                </div>

                <div class="w-px h-8 bg-white/10 flex-shrink-0"></div>

                {{-- Aksi --}}
                <div class="flex items-center gap-2 flex-1 flex-wrap">
                    {{-- Set nilai --}}
                    <button type="button" @click="openBulkModal('set')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Set Nilai
                    </button>

                    {{-- Tambah hari --}}
                    <button type="button" @click="openBulkModal('add')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Hari
                    </button>

                    {{-- Kurangi hari --}}
                    <button type="button" @click="openBulkModal('subtract')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-white text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                        </svg>
                        Kurangi Hari
                    </button>
                </div>

                {{-- Batalkan seleksi --}}
                <button type="button" @click="clearSelection()"
                    class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                    title="Batalkan seleksi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

    </div>{{-- end x-data --}}

    {{-- ================================================================
         ALPINE SCRIPT
    ================================================================ --}}
    <script>
        function quotaManager() {
            return {
                // ── State seleksi ──────────────────────────────────────────
                selectedIds: [],
                // Total semua data di semua halaman (bukan hanya halaman aktif)
                totalRows: {{ $userLeaveBalances->total() }},

                // Map userId → { name } untuk keperluan UI chip di modal bulk (halaman aktif saja)
                rowData: {
                    @foreach ($userLeaveBalances as $balance)
                        {{ $balance->user->id }}: {
                            name: '{{ addslashes(Str::title($balance->user->name)) }}',
                            remaining: {{ $balance->remaining }},
                            used: {{ $balance->used }},
                        },
                    @endforeach
                },

                // Semua user dari SEMUA halaman (untuk "pilih semua")
                allRowData: {
                    @foreach ($allUserBalances as $item)
                        {{ $item->user_id }}: {
                            name: '{{ addslashes(Str::title($item->name)) }}',
                        },
                    @endforeach
                },

                // ── Modals ────────────────────────────────────────────────
                showResetZone: false,

                editModal: {
                    show: false,
                    name: '',
                    total: 0,
                    used: 0,
                    remaining: 0,
                    newRemaining: 0,
                    action: '',
                },

                bulkModal: {
                    show: false,
                    mode: 'set', // 'set' | 'add' | 'subtract'
                    value: 0,
                },

                confirmDialog: {
                    show: false,
                    title: '',
                    message: '',
                    type: 'danger',
                    form: null,
                },

                // ── Init ──────────────────────────────────────────────────
                init() {
                    // Sinkronisasi state checkbox "pilih semua" saat mount
                    this.$watch('selectedIds', () => {
                        const cb = this.$refs.checkboxAll;
                        if (!cb) return;
                        cb.checked = this.selectedIds.length === this.totalRows && this.totalRows > 0;
                        cb.indeterminate = this.selectedIds.length > 0 && this.selectedIds.length < this.totalRows;
                    });
                },

                // ── Seleksi ───────────────────────────────────────────────
                toggleSelect(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx === -1) {
                        this.selectedIds.push(id);
                    } else {
                        this.selectedIds.splice(idx, 1);
                    }
                },

                toggleSelectAll(checked) {
                    if (checked) {
                        // Pilih SEMUA user dari seluruh halaman
                        this.selectedIds = Object.keys(this.allRowData).map(Number);
                    } else {
                        this.selectedIds = [];
                    }
                },

                clearSelection() {
                    this.selectedIds = [];
                    if (this.$refs.checkboxAll) {
                        this.$refs.checkboxAll.checked = false;
                    }
                },

                // ── Edit individual ───────────────────────────────────────
                openEditModal(data) {
                    this.editModal = {
                        show: true,
                        ...data,
                        newRemaining: data.remaining
                    };
                },

                closeEditModal() {
                    this.editModal.show = false;
                },

                // ── Bulk ──────────────────────────────────────────────────
                openBulkModal(mode) {
                    this.bulkModal = {
                        show: true,
                        mode,
                        value: 0
                    };
                },

                submitBulk(event) {
                    if (this.selectedIds.length === 0) return;
                    if (!this.bulkModal.value && this.bulkModal.value !== 0) return;

                    const modeLabel = {
                        set: `diset ke ${this.bulkModal.value} hari`,
                        add: `ditambah ${this.bulkModal.value} hari`,
                        subtract: `dikurangi ${this.bulkModal.value} hari`,
                    } [this.bulkModal.mode];

                    this.showConfirmation(event, {
                        title: 'Konfirmasi Ubah Kuota Massal',
                        message: `Kuota ${this.selectedIds.length} karyawan yang dipilih akan ${modeLabel}. Lanjutkan?`,
                        type: this.bulkModal.mode === 'set' ? 'info' : (this.bulkModal.mode === 'add' ? 'info' :
                            'warning'),
                        // Setelah konfirmasi, submit form
                        form: event.target,
                    });
                },

                // ── Konfirmasi universal ──────────────────────────────────
                showConfirmation(event, options) {
                    if (event && event.preventDefault) event.preventDefault();
                    this.confirmDialog = {
                        show: true,
                        title: options.title || 'Konfirmasi',
                        message: options.message || 'Apakah Anda yakin?',
                        type: options.type || 'danger',
                        form: options.form || (event ? event.target : null),
                    };
                },

                confirmAction() {
                    if (this.confirmDialog.form) {
                        this.confirmDialog.form.submit();
                        // Reset seleksi setelah submit bulk
                        this.selectedIds = [];
                        this.bulkModal.show = false;
                    }
                    this.confirmDialog.show = false;
                },
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <x-toast-notification />
</x-app-layout>
