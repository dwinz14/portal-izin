<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Ubah Pengganti') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Daftar cuti yang sudah final approved di bawah tanggung jawab anda. Pengganti hanya bisa diganti
                    selama periode cuti belum terlewat.
                </p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        modalOpen: false,
        leaveId: null,
        leaveLabel: '',
        candidates: [],
        loadingCandidates: false,
        selectedPenggantiId: '',
        confirmText: '',
        submitting: false,
        get canConfirm() { return this.confirmText === 'GANTI' && this.selectedPenggantiId !== '' },
        openModal(id, label) {
            this.leaveId = id;
            this.leaveLabel = label;
            this.candidates = [];
            this.selectedPenggantiId = '';
            this.confirmText = '';
            this.submitting = false;
            this.modalOpen = true;
            this.loadingCandidates = true;
            fetch(`{{ url('cuti') }}/${id}/pengganti/eligible`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.candidates = data;
                    this.loadingCandidates = false;
                })
                .catch(() => { this.loadingCandidates = false; });
        }
    }" class="max-w-6xl mx-auto pb-10 space-y-4">

        <x-toast-notification />

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

        {{-- ═══ TABEL DAFTAR CUTI ═══ --}}
        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr
                            class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Pemohon</th>
                            <th class="px-6 py-3">Jenis Cuti</th>
                            <th class="px-6 py-3">Periode</th>
                            <th class="px-6 py-3">Pengganti Saat Ini</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse ($leaves as $leave)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-100">
                                    {{ ucwords($leave->user->name) }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $leave->leaveType->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}
                                    s/d
                                    {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {{ ucwords($leave->pengganti->name ?? '-') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                        @click="openModal({{ $leave->id }}, '{{ addslashes(ucwords($leave->user->name)) }} ({{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }})')"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-lg hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M16 15v-1a4 4 0 00-4-4H8m0 0l3-3m-3 3l3 3m10 3v1a4 4 0 01-4 4h-4m0 0l3 3m-3-3l3-3" />
                                        </svg>
                                        Ubah Pengganti
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    Tidak ada cuti yang bisa diubah penggantinya saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaves->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>

        {{-- ═══ MODAL KONFIRMASI GANTI PENGGANTI ═══ --}}
        <div x-show="modalOpen" x-cloak x-transition
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/70 dark:bg-slate-950/80 backdrop-blur-md"
            x-on:keydown.escape.window="modalOpen = false">

            <div class="bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden relative border border-amber-500/30 dark:border-amber-500/40"
                @click.stop x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-8">

                {{-- Header --}}
                <div class="bg-amber-600 px-8 py-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0 backdrop-blur-sm border border-white/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 15v-1a4 4 0 00-4-4H8m0 0l3-3m-3 3l3 3m10 3v1a4 4 0 01-4 4h-4m0 0l3 3m-3-3l3-3" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-white text-xl tracking-wide">Ubah Pengganti</h3>
                            <p class="text-amber-100 text-sm font-medium mt-0.5 truncate" x-text="leaveLabel"></p>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 space-y-5">
                    {{-- Pilih pengganti baru --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-200 mb-2">
                            Pilih Pengganti Baru
                        </label>

                        <template x-if="loadingCandidates">
                            <p class="text-sm text-slate-400 italic">Memuat daftar calon pengganti...</p>
                        </template>

                        <template x-if="!loadingCandidates && candidates.length === 0">
                            <p class="text-sm font-medium text-rose-600 dark:text-rose-400">
                                Tidak ada calon pengganti yang memenuhi aturan untuk pemohon ini.
                            </p>
                        </template>

                        <select x-show="!loadingCandidates && candidates.length > 0" x-model="selectedPenggantiId"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 transition-all">
                            <option value="">-- Pilih user pengganti --</option>
                            <template x-for="c in candidates" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Input verifikasi ketik GANTI --}}
                    <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 shadow-inner">
                        <label class="block text-sm font-medium text-slate-300 mb-3 text-center">
                            Ketik kata konfirmasi <span
                                class="text-amber-400 font-bold font-mono text-base bg-amber-400/10 px-2 py-0.5 rounded border border-amber-400/20 mx-1">GANTI</span>
                            di bawah ini untuk melanjutkan:
                        </label>
                        <input type="text" x-model="confirmText" placeholder="Ketik persis huruf kapital..."
                            autocomplete="off"
                            class="w-full px-4 py-3 bg-slate-800 border-2 border-slate-700 rounded-xl text-center text-xl font-bold font-mono text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 transition-all">
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="px-8 py-5 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-600 flex flex-col-reverse sm:flex-row items-center gap-3">
                    <button type="button" @click="modalOpen = false"
                        class="w-full sm:w-1/3 px-5 py-3.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-gray-300 text-sm font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700 transition-colors">
                        Batal
                    </button>

                    <form :action="`{{ url('cuti') }}/${leaveId}/replace-pengganti`" method="POST"
                        class="w-full sm:w-2/3" @submit="submitting = true">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="new_pengganti_id" :value="selectedPenggantiId">
                        <input type="hidden" name="confirmation" :value="confirmText">
                        <button type="submit" :disabled="!canConfirm || submitting"
                            class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-amber-600 text-white text-sm font-black tracking-wide rounded-xl hover:bg-amber-700 shadow-lg shadow-amber-200 dark:shadow-amber-900 focus:ring-4 focus:ring-amber-100 dark:focus:ring-amber-900 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                            <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"
                                x-cloak>
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 15v-1a4 4 0 00-4-4H8m0 0l3-3m-3 3l3 3m10 3v1a4 4 0 01-4 4h-4m0 0l3 3m-3-3l3-3" />
                            </svg>
                            <span x-text="submitting ? 'MEMPROSES...' : 'UBAH PENGGANTI'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
