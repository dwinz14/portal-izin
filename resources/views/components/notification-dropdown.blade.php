@props(['align' => 'right'])

@php
    $alignmentClasses = [
        'right' => 'origin-top-right right-0',
        'left' => 'origin-top-left left-0',
    ];
@endphp

<div class="relative" x-data="notificationDropdown()">
    <button @click="open = !open" type="button"
        class="relative flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 p-2 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors duration-200">
        <span class="sr-only">Open notifications menu</span>
        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5 5v-5zM15 12V7a3 3 0 00-6 0v5l-5 5h16l-5-5z"></path>
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
        </span>
    </button>

    <div x-show="open" @click.away="open = false"
        class="absolute z-50 mt-2 w-80 {{ $alignmentClasses[$align] ?? 'origin-top-right right-0' }} rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700"
        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95">

        <div class="py-1">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifikasi</h3>
                    <div class="flex items-center space-x-2">
                        <span x-show="unreadCount > 0" class="text-xs text-gray-500 dark:text-gray-400">
                            <span x-text="unreadCount"></span> belum dibaca
                        </span>
                        <button @click="markAllAsRead()" x-show="unreadCount > 0"
                            class="text-xs text-blue-600 hover:text-blue-500 underline">Tandai semua dibaca</button>
                    </div>
                </div>
            </div>

            <div id="notification-list" class="max-h-80 overflow-y-auto">
                <!-- Notifications will be loaded here -->
                <div x-show="loading" class="px-4 py-4 text-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                    <p class="text-xs text-gray-500 mt-2">Memuat notifikasi...</p>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <a href="{{ route('notifications.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                        Lihat semua notifikasi
                    </a>
                    <button @click="refreshNotifications()" class="text-xs text-gray-500 hover:text-gray-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationDropdown', () => ({
            open: false,
            unreadCount: 0,
            notifications: [],
            loading: false,

            init() {
                this.loadUnreadCount();
                this.loadNotifications();
                // Refresh every 60 seconds
                setInterval(() => {
                    this.loadUnreadCount();
                    // Only refresh notifications if dropdown is closed
                    if (!this.open) {
                        this.loadNotifications();
                    }
                }, 60000);
            },

            loadUnreadCount() {
                fetch('/notifications/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        this.unreadCount = data.count;
                    })
                    .catch(error => {
                        console.error('Error loading unread count:', error);
                    });
            },

            loadNotifications() {
                if (this.loading) return;
                this.loading = true;

                fetch('/notifications/latest')
                    .then(response => response.json())
                    .then(data => {
                        this.notifications = data;
                        this.renderNotifications();
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        this.loading = false;
                    });
            },

            refreshNotifications() {
                this.loadUnreadCount();
                this.loadNotifications();
            },

            renderNotifications() {
                const list = document.getElementById('notification-list');
                if (!list) return;

                // Remove loading indicator
                const loadingDiv = list.querySelector('[x-show="loading"]');
                if (loadingDiv) loadingDiv.remove();

                if (this.notifications.length === 0) {
                    list.innerHTML =
                        '<div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">' +
                        '<svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 12V7a3 3 0 00-6 0v5l-5 5h16l-5-5z"></path>' +
                        '</svg>' +
                        '<p>Tidak ada notifikasi</p>' +
                        '</div>';
                    return;
                }

                // Helper untuk escape HTML (mencegah XSS)
                const escapeHtml = (str) => {
                    if (!str) return '';
                    return str.replace(/[&<>]/g, function(m) {
                        if (m === '&') return '&amp;';
                        if (m === '<') return '&lt;';
                        if (m === '>') return '&gt;';
                        return m;
                    });
                };

                list.innerHTML = this.notifications.map(notification => `
                <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-150 border-b border-gray-100 dark:border-gray-600 last:border-b-0 ${notification.read_at !== null ? 'bg-gray-50/50 dark:bg-gray-700/50' : 'bg-white dark:bg-gray-800'}" onclick="markAsRead(${notification.id})">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br ${notification.read_at !== null ? 'from-gray-400 to-gray-500' : 'from-blue-500 to-blue-600'} flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 12V7a3 3 0 00-6 0v5l-5 5h16l-5-5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate ${notification.read_at !== null ? 'text-gray-600 dark:text-gray-400' : ''}">
                                        ${notification.data?.title}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                        ${notification.data?.message}
                                    </p>
                                </div>
                                ${!notification.read_at !== null ? '<div class="ml-2 flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>' : ''}
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    ${this.timeAgo(notification.created_at)}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            },

            markAllAsRead() {
                if (this.unreadCount === 0) return;

                fetch('/notifications/mark-all-read', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.unreadCount = 0;
                            this.notifications.forEach(n => n.is_read = true);
                            this.renderNotifications();
                            // Show success feedback
                            this.showToast('Semua notifikasi telah ditandai dibaca');
                        } else {
                            this.showToast('Gagal menandai notifikasi', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error marking all as read:', error);
                        this.showToast('Terjadi kesalahan', 'error');
                    });
            },

            showToast(message, type = 'success') {
                // Simple toast implementation - you can enhance this
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white text-sm z-50 ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            },

            timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) return 'Baru saja';
                if (diffInSeconds < 3600)
                    return `${Math.floor(diffInSeconds / 60)} menit yang lalu`;
                if (diffInSeconds < 86400)
                    return `${Math.floor(diffInSeconds / 3600)} jam yang lalu`;
                return `${Math.floor(diffInSeconds / 86400)} hari yang lalu`;
            }
        }));

        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the notification in the Alpine data
                        const notificationDropdown = document.querySelector(
                            '[x-data="notificationDropdown()"]')._x_dataStack[0];
                        if (notificationDropdown) {
                            const notification = notificationDropdown.notifications.find(n => n.id ===
                                notificationId);
                            if (notification) {
                                notification.is_read = true;
                                notificationDropdown.unreadCount = Math.max(0, notificationDropdown
                                    .unreadCount - 1);
                                notificationDropdown.renderNotifications();
                            }
                        }
                    } else {
                        showToast('Gagal menandai notifikasi', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error marking as read:', error);
                    showToast('Terjadi kesalahan', 'error');
                });
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white text-sm z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    });
</script>
