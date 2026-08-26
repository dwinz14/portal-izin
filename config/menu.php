<?php

return [
    'items' => [
        // -----------------------------------------------------------------
        // Dashboard
        // -----------------------------------------------------------------
        [
            'type' => 'item',
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>',
            'roles' => ['super_admin', 'hrd', 'kabag-pincab', 'staff', 'kasie', 'direksi'],
            'active_pattern' => 'dashboard*',
        ],

        // -----------------------------------------------------------------
        // Pengajuan
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Pengajuan',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>',
            'roles' => ['hrd', 'kabag-pincab', 'staff', 'kasie'],
            'active_pattern' => [
                'cuti.index*',
                'kehadiran*',
            ],
            'children' => [
                [
                    'name' => 'Pengajuan Cuti',
                    'route' => 'cuti.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                    'roles' => ['hrd', 'kabag-pincab', 'staff', 'kasie'],
                    'active_pattern' => 'cuti.index*',
                ],
                [
                    'name' => 'Pengajuan Kehadiran',
                    'route' => 'kehadiran.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                    'roles' => ['hrd', 'kabag-pincab', 'staff', 'kasie'],
                    'active_pattern' => 'kehadiran*',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // Jadwal
        // -----------------------------------------------------------------
        [
            'name' => 'Jadwal Pengganti',
            'route' => 'replacements.index',
            'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
            'roles' => ['kabag-pincab', 'staff', 'kasie', 'hrd'],
            'active_pattern' => 'replacements.index*',
        ],

        // -----------------------------------------------------------------
        // Persetujuan
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Persetujuan',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>',
            'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie', 'staff'],
            'active_pattern' => [
                'approval.index*',
                'approval-kehadiran.index*',
            ],
            'children' => [
                [
                    'name' => 'Persetujuan Cuti',
                    'route' => 'approval.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie', 'staff'],
                    'active_pattern' => 'approval.index*',
                    // Isi dari controller/service bila tersedia.
                    'badge_count' => 0,
                    'badge_tone' => 'danger',
                ],
                [
                    'name' => 'Persetujuan Kehadiran',
                    'route' => 'approval-kehadiran.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie'],
                    'active_pattern' => 'approval-kehadiran.index*',
                    'badge_count' => 0,
                    'badge_tone' => 'danger',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // Riwayat
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Riwayat',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>',
            'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie', 'staff'],
            'active_pattern' => [
                'approval.history*',
                'approval-kehadiran.history*',
            ],
            'children' => [
                [
                    'name' => 'Persetujuan Cuti',
                    'route' => 'approval.history',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie', 'staff'],
                    'active_pattern' => 'approval.history*',
                ],
                [
                    'name' => 'Persetujuan Kehadiran',
                    'route' => 'approval-kehadiran.history',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'roles' => ['direksi', 'hrd', 'kabag-pincab', 'kasie'],
                    'active_pattern' => 'approval-kehadiran.history*',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // Master Data
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Master Data',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0m-9.75 0h9.75" />
            </svg>',
            'roles' => ['super_admin'],
            'active_pattern' => [
                'admin.users*',
                'admin.offices*',
                'admin.divisions*',
                'admin.positions*',
                'admin.leave-types*',
                'admin.user-activity*',
            ],
            'children' => [
                [
                    'name' => 'Manajemen Pengguna',
                    'route' => 'admin.users.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.users*',
                ],
                [
                    'name' => 'Master Kantor',
                    'route' => 'admin.offices.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5h4v5" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.offices*',
                ],
                [
                    'name' => 'Master Divisi',
                    'route' => 'admin.divisions.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5V4H2v16h5m10 0v-5H7v5m10 0H7M6 8h.01M10 8h.01M14 8h.01M18 8h.01" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.divisions*',
                ],
                [
                    'name' => 'Master Jabatan',
                    'route' => 'admin.positions.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.positions*',
                ],
                [
                    'name' => 'Master Jenis Cuti',
                    'route' => 'admin.leave-types.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.leave-types*',
                ],
                [
                    'name' => 'Aktivitas Pengguna',
                    'route' => 'admin.user-activity.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h2a2 2 0 012 2v14m8 0h2a2 2 0 002-2v-7a2 2 0 00-2-2h-2a2 2 0 00-2 2v9" /></svg>',
                    'roles' => ['super_admin'],
                    'active_pattern' => 'admin.user-activity*',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // Laporan
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Laporan',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>',
            'roles' => ['hrd', 'super_admin'],
            'active_pattern' => [
                'hrd.rekap.index*',
                'hrd.kehadiran*',
            ],
            'children' => [
                [
                    'name' => 'Rekap Cuti',
                    'route' => 'hrd.rekap.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
                    'roles' => ['hrd', 'super_admin'],
                    'active_pattern' => 'hrd.rekap.index*',
                ],
                [
                    'name' => 'Rekap Kehadiran',
                    'route' => 'hrd.kehadiran.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002-2z" /></svg>',
                    'roles' => ['hrd', 'super_admin'],
                    'active_pattern' => 'hrd.kehadiran*',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // Manajemen HR
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'Manajemen HR',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6V3m0 18v-3m6-6h3M3 12h3m10.243-4.243l2.121-2.121M5.636 18.364l2.121-2.121m0-8.486L5.636 5.636m12.728 12.728l-2.121-2.121M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
            </svg>',
            'roles' => ['hrd', 'super_admin'],
            'active_pattern' => [
                'hrd.quota.index*',
                'hrd.holidays*',
            ],
            'children' => [
                [
                    'name' => 'Kuota Cuti',
                    'route' => 'hrd.quota.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>',
                    'roles' => ['hrd', 'super_admin'],
                    'active_pattern' => 'hrd.quota.index*',
                ],
                [
                    'name' => 'Hari Libur',
                    'route' => 'hrd.holidays.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
                    'roles' => ['hrd', 'super_admin'],
                    'active_pattern' => 'hrd.holidays*',
                ],
            ],
        ],

        // -----------------------------------------------------------------
        // System
        // -----------------------------------------------------------------
        [
            'type' => 'section',
            'name' => 'System',
            'icon' => '<svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>',
            'roles' => ['hrd', 'super_admin'],
            'active_pattern' => 'database*',
            'children' => [
                [
                    'name' => 'Database',
                    'route' => 'database.index',
                    'icon' => '<svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8-4 8 4M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>',
                    'roles' => ['hrd', 'super_admin'],
                    'active_pattern' => 'database*',
                ],
            ],
        ],
    ],
];
