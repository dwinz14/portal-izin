        @if ($pendingLeaves->count() > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Proses Approval Cuti Sedang
                    Berlangsung</h3>
                <div class="space-y-6">
                    @foreach ($pendingLeaves as $leave)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        Pengajuan Cuti {{ $leave->total_hari }} Hari
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} -
                                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($leave->status_final === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                        @elseif($leave->status_final === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                                        {{ ucfirst($leave->status_final) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Horizontal Stepper -->
                            <div class="relative">
                                @php
                                    $flow = config('approval_flow.' . $leave->user->role);
                                    $steps = ['Pengajuan'];
                                    foreach ($flow as $step) {
                                        $steps[] = 'Approval ' . ucfirst(str_replace('_', ' ', $step));
                                    }
                                    $currentStep = 0;
                                    $stepStatuses = [];
                                    foreach ($steps as $index => $stepName) {
                                        if ($index == 0) {
                                            $stepStatuses[] = 'approved';
                                            $currentStep = 1;
                                        } else {
                                            $approval = $leave->approvals->where('step', $index)->first();
                                            if ($approval) {
                                                $stepStatuses[] = $approval->status;
                                                if ($approval->status === 'pending') {
                                                    $currentStep = $index + 1;
                                                }
                                            } else {
                                                $stepStatuses[] = 'pending';
                                                $currentStep = $index + 1;
                                            }
                                        }
                                    }
                                @endphp

                                <div class="flex items-center justify-between">
                                    @foreach ($steps as $index => $stepName)
                                        <div class="flex flex-col items-center flex-1">
                                            <!-- Step Circle -->
                                            <div
                                                class="relative flex items-center justify-center w-8 h-8 rounded-full
                                                @if ($stepStatuses[$index] === 'approved') bg-green-500 text-white
                                                @elseif($stepStatuses[$index] === 'pending') bg-yellow-500 text-white
                                                @else bg-red-500 text-white @endif">
                                                @if ($stepStatuses[$index] === 'approved')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($stepStatuses[$index] === 'pending')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <!-- Step Label -->
                                            <span
                                                class="mt-2 text-xs font-medium text-center
                                                @if ($stepStatuses[$index] === 'approved') text-green-700 dark:text-green-300
                                                @elseif($stepStatuses[$index] === 'pending') text-yellow-700 dark:text-yellow-300
                                                @else text-red-700 dark:text-red-300 @endif">
                                                {{ $stepName }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
