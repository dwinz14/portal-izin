@if ($pendingLeaves->count() > 0)
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Proses Approval Cuti Sedang
        Berlangsung</h3>
    <div class="space-y-6">
        @foreach ($pendingLeaves as $leave)
        @php
        // Langkah dibangun dari rantai approval yang SEBENARNYA (bukan dari config per role),
        // sehingga sesuai untuk cuti dengan/tanpa pengganti dan atasan siapa pun yang dipilih.
        $approvals = $leave->approvals->sortBy('step')->values();
        $hasPengganti = (bool) $leave->pengganti_id;

        $steps = [
        [
        'label' => 'Pengajuan',
        'name' => null,
        'state' => 'done',
        'caption' => $leave->created_at?->translatedFormat('d M, H:i'),
        ],
        ];

        $currentFound = false; // sudah ada langkah yang sedang menunggu
        $blocked = false; // ada langkah ditolak: langkah sesudahnya tidak akan diproses

        foreach ($approvals as $i => $approval) {
        $isRevision =
        $leave->is_revision_pending &&
        (int) $leave->revision_by_approval_id === (int) $approval->id;

        $state = match (true) {
        $approval->status === 'approved' => 'done',
        $approval->status === 'rejected' => 'rejected',
        $blocked => 'waiting',
        $isRevision => 'revision',
        !$currentFound => 'current',
        default => 'waiting',
        };

        if (in_array($state, ['current', 'revision'], true)) {
        $currentFound = true;
        }
        if ($state === 'rejected') {
        $blocked = true;
        }

        $at = $approval->updated_at?->translatedFormat('d M, H:i');

        $steps[] = [
        // Langkah pertama adalah pengganti HANYA bila cuti ini punya pengganti
        'label' => $hasPengganti && $i === 0 ? 'Pengganti' : 'Atasan',
        'name' => ucwords($approval->approver->name ?? '-'),
        'state' => $state,
        'caption' => match ($state) {
        'done' => "Disetujui {$at}",
        'rejected' => "Ditolak {$at}",
        'current' => 'Menunggu persetujuan',
        'revision' => 'Usulan revisi tanggal, menunggu respons Anda',
        default => 'Menunggu giliran',
        },
        ];
        }

        // Ringkasan posisi saat ini
        $focus = collect($steps)->first(
        fn($s) => in_array($s['state'], ['current', 'revision', 'rejected'], true),
        );
        $summary = match ($focus['state'] ?? null) {
        'current' => "Saat ini menunggu persetujuan {$focus['label']}: {$focus['name']}",
        'revision' => "{$focus['label']} {$focus['name']} mengusulkan revisi tanggal. Menunggu respons Anda.",
        'rejected' => "Ditolak oleh {$focus['label']}: {$focus['name']}",
        default => null,
        };

        if ($leave->is_revision_pending) {
        $statusLabel = 'Revisi tanggal';
        $statusClass =
        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
        } else {
        $statusLabel = match ($leave->status_final) {
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        default => ucfirst($leave->status_final),
        };
        $statusClass = match ($leave->status_final) {
        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        default => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        };
        }
        @endphp

        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                        Pengajuan Cuti {{ $leave->total_hari }} Hari
                        @unless ($hasPengganti)
                        <span
                            class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            Tanpa pengganti
                        </span>
                        @endunless
                    </h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            @if ($summary)
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{{ $summary }}</p>
            @endif

            <!-- Horizontal Stepper -->
            <div class="flex items-start">
                @foreach ($steps as $step)
                @php
                [$circleClass, $textClass] = match ($step['state']) {
                'done' => ['bg-green-500 text-white', 'text-green-700 dark:text-green-300'],
                'current' => ['bg-yellow-500 text-white', 'text-yellow-700 dark:text-yellow-300'],
                'revision' => ['bg-amber-500 text-white', 'text-amber-700 dark:text-amber-300'],
                'rejected' => ['bg-red-500 text-white', 'text-red-700 dark:text-red-300'],
                default => [
                'bg-gray-300 text-gray-500 dark:bg-gray-700 dark:text-gray-300',
                'text-gray-500 dark:text-gray-400',
                ],
                };
                @endphp
                <div class="relative flex flex-col items-center flex-1 min-w-0 px-1">
                    {{-- Garis penghubung ke langkah berikutnya --}}
                    @unless ($loop->last)
                    <div style="left: 50%"
                        class="absolute top-4 w-full h-0.5 {{ $step['state'] === 'done' ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                    </div>
                    @endunless

                    <!-- Step Circle -->
                    <div class="relative flex items-center justify-center w-8 h-8 rounded-full {{ $circleClass }}">
                        @if ($step['state'] === 'done')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @elseif ($step['state'] === 'current')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @elseif ($step['state'] === 'revision')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        @elseif ($step['state'] === 'rejected')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        @else
                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                        @endif
                    </div>

                    <!-- Step Label -->
                    <span class="mt-2 text-xs font-semibold text-center {{ $textClass }}">
                        {{ $step['label'] }}
                    </span>
                    @if ($step['name'])
                    <span class="text-xs text-center text-gray-700 dark:text-gray-200 truncate max-w-full">
                        {{ $step['name'] }}
                    </span>
                    @endif
                    <span class="mt-0.5 text-[11px] text-center text-gray-500 dark:text-gray-400">
                        {{ $step['caption'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif