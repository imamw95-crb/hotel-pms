<?php

return [
    'items' => [
        // Dashboard
        [
            'label' => 'Dashboard Owner',
            'icon' => 'chart-line',
            'route' => 'dashboard',
            'permission' => 'manage_users',
        ],
        [
            'label' => 'Dashboard Kamar',
            'icon' => 'th-large',
            'route' => 'rooms.dashboard',
        ],

        // TRANSAKSI
        [
            'label' => 'TRANSAKSI',
            'icon' => 'exchange-alt',
            'children' => [
                [
                    'label' => 'Issue Card',
                    'route' => 'issue-card.index',
                    'permission' => 'issue_card',
                ],
                [
                    'label' => 'Reservasi',
                    'route' => 'reservations.index',
                    'permission' => 'view_reservations',
                ],
                [
                    'label' => 'Check-in',
                    'route' => 'checkin.index',
                    'permission' => 'checkin',
                ],
            ],
        ],

        // BOOKING
        [
            'label' => 'BOOKING',
            'icon' => 'calendar-plus',
            'children' => [
                [
                    'label' => 'Booking Single',
                    'route' => 'booking.create',
                    'permission' => 'create_booking',
                ],
                [
                    'label' => 'Booking Group',
                    'route' => 'booking.group.create',
                    'permission' => 'create_booking_group',
                ],
            ],
        ],

        // MANAJEMEN
        [
            'label' => 'MANAJEMEN',
            'icon' => 'bed',
            'children' => [
                [
                    'label' => 'Kelola Kamar',
                    'route' => 'rooms.index',
                    'permission' => 'view_rooms',
                ],
                [
                    'label' => 'Tipe Kamar',
                    'route' => 'room-types.index',
                    'permission' => 'view_room_types',
                ],
                [
                    'label' => 'Master Tamu',
                    'route' => 'guests.index',
                    'permission' => 'manage_guests',
                ],
            ],
        ],

        // LAPORAN
        [
            'label' => 'LAPORAN',
            'icon' => 'file-chart-line',
            'children' => [
                [
                    'label' => 'Night Audit',
                    'route' => 'reports.night-audit',
                    'permission' => 'view_reports',
                ],
                [
                    'label' => 'Guest List',
                    'route' => 'reports.guest-list',
                    'permission' => 'view_reports',
                ],
                [
                    'label' => 'Okupansi',
                    'route' => 'reports.occupancy',
                    'permission' => 'view_reports',
                ],
                [
                    'label' => 'Pendapatan',
                    'route' => 'reports.revenue',
                    'permission' => 'view_reports',
                ],
                [
                    'label' => 'Lap. Reservasi',
                    'route' => 'reports.reservations',
                    'permission' => 'view_reports',
                ],
            ],
        ],

        // Admin Panel
        [
            'label' => 'Admin Panel',
            'icon' => 'gear',
            'children' => [
                [
                    'label' => 'Permission Dashboard',
                    'route' => 'admin.permissions.dashboard',
                    'permission' => 'manage_users',
                ],
                [
                    'label' => 'Permissions List',
                    'route' => 'admin.permissions.index',
                    'permission' => 'manage_users',
                ],
                [
                    'label' => 'User Permissions',
                    'route' => 'admin.permissions.user-permissions',
                    'permission' => 'manage_users',
                ],
                [
                    'label' => 'Manage Users',
                    'route' => 'admin.users.index',
                    'permission' => 'manage_users',
                ],
                [
                    'label' => 'Backup Database',
                    'route' => 'admin.backups.index',
                    'permission' => 'manage_users',
                ],
            ],
        ],

    ],
];
