<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class=" border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Daftar Notifikasi') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Lihat semua notifikasi Anda.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ $notifications->where('is_read', false)->count() }} Belum Dibaca
                </span>
                @if ($notifications->where('is_read', false)->count() > 0)
                    <button onclick="markAllAsRead()" type="button"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-xs font-medium rounded-full shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Notifications List -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $title = $data['title'] ?? 'Notifikasi';
                        $message = $data['message'] ?? '';
                        $isRead = !is_null($notification->read_at);
                    @endphp
                    <div
                        class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 {{ $isRead ? 'opacity-75' : '' }}">
                                    {{ $title }}
                                </h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-1 {{ $isRead ? 'opacity-75' : '' }}">
                                    {{ $message }}
                                </p>
                                <div class="flex items-center mt-3 space-x-4">
                                    <small class="text-gray-500 dark:text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                    @if (!$isRead)
                                        <span class="flex h-2 w-2">
                                            <span
                                                class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if (!$isRead)
                                    <button onclick="markAsRead({{ $notification->id }})"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-full shadow-sm transition">
                                        Tandai Dibaca
                                    </button>
                                @else
                                    <span
                                        class="text-xs text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 px-3 py-1.5 rounded-full">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Sudah dibaca
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-5 5v-5zM15 12V7a3 3 0 00-6 0v5l-5 5h16l-5-5z"></path>
                        </svg>
                        <p class="mt-4 text-gray-500">Tidak ada notifikasi.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay"
        class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-gray-700 dark:text-gray-300 font-medium">Memproses...</span>
            </div>
        </div>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
                type === 'success'
                    ? 'bg-green-500 text-white'
                    : 'bg-red-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Mark single notification as read
        function markAsRead(notificationId) {
            showLoading();

            fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showToast('Notifikasi berhasil ditandai sebagai dibaca');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast('Terjadi kesalahan, silakan coba lagi', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan, silakan coba lagi', 'error');
                });
        }

        // Mark all notifications as read
        function markAllAsRead() {
            if (!confirm('Tandai semua notifikasi sebagai dibaca?')) {
                return;
            }

            showLoading();

            fetch('/notifications/mark-all-read', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showToast('Semua notifikasi berhasil ditandai sebagai dibaca');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast('Terjadi kesalahan, silakan coba lagi', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan, silakan coba lagi', 'error');
                });
        }
    </script>

    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-item {
            animation: slideInRight 0.3s ease-out;
        }
    </style>
</x-app-layout>
