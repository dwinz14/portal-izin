<x-app-layout>
    <!-- Header -->
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Left -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="border-l-4 border-primary-600 pl-4">User Management</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola aktivitas pengguna dan persetujuan registrasi.
                </p>
            </div>

            <!-- Right -->
            <div class="relative">
                <label for="feature-select" class="sr-only">Pilih Fitur</label>
                <div class="relative group">
                    <select id="feature-select"
                        class="appearance-none w-56 px-4 py-2 pr-10 text-sm font-medium bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 dark:bg-slate-800 dark:border-slate-700 dark:text-gray-100 transition-all duration-200 hover:border-gray-400 dark:hover:border-slate-600 peer">
                        <option value="user-activity" selected>User Activity</option>
                        <option value="registration-approvals">Registration Approvals</option>
                    </select>

                    <!-- Dropdown Arrow -->
                    <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none transition-transform duration-200 peer-focus:rotate-180"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>

                    <!-- Notification Badge -->
                    @if (isset($pendingCount) && $pendingCount > 0)
                        <span id="notif-badge"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 shadow-sm">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Main Content -->
    <div
        class="mt-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden transition-all duration-300">

        <!-- User Activity -->
        <div id="user-activity-content" class="feature-content p-6 fade-in">
            @include('admin.user-management.partials.user-activity')
        </div>

        <!-- Registration Approvals -->
        <div id="registration-approvals-content" class="feature-content hidden p-6">
            @include('admin.user-management.partials.registration-approvals')
        </div>
    </div>
    <x-toast-notification />
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('feature-select');
            const contents = document.querySelectorAll('.feature-content');
            const badge = document.getElementById('notif-badge');

            select.addEventListener('change', (e) => {
                const selected = e.target.value;

                // Toggle content visibility
                contents.forEach(c => {
                    if (c.id === selected + '-content') {
                        c.classList.remove('hidden');
                        c.classList.add('fade-in');
                    } else {
                        c.classList.add('hidden');
                        c.classList.remove('fade-in');
                    }
                });

                // Sembunyikan badge jika user buka halaman approval
                if (badge) {
                    badge.classList.toggle('hidden', selected === 'registration-approvals');
                }
            });
        });
    </script>

    <style>
        /* Efek transisi lembut saat ganti fitur */
        .fade-in {
            animation: fadeIn 0.25s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dark mode badge kontras */
        [data-theme="dark"] #notif-badge {
            background-color: #ef4444;
            color: #fff;
        }
    </style>
</x-app-layout>
