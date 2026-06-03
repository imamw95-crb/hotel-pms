<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Menu items for the Hotel PMS sidebar navigation.
    | Each item can have: label, icon, route, permission, roles, children.
    |
    */

    'items' => [

        // ─── Dashboard ───────────────────────────────────────────────
        [
            'label' => 'Dashboard',
            'icon' => 'tachometer-alt',
            'route' => 'dashboard',
            'permission' => 'manage_users',
        ],

        // ─── Reservasi (semua role termasuk user_manager) ────────────
        [
            'label' => 'Reservasi',
            'icon' => 'calendar-alt',
            'route' => 'reservations.index',
            'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
        ],

        // ─── Front Desk ──────────────────────────────────────────────
        [
            'label' => 'Front Desk',
            'icon' => 'concierge-bell',
            'roles' => ['owner', 'admin', 'frontoffice'],
            'children' => [
                [
                    'label' => 'Check-in',
                    'route' => 'checkin.index',
                    'permission' => 'checkin',
                ],
                [
                    'label' => 'Checkout',
                    'route' => 'checkout.index',
                    'permission' => 'checkout',
                ],
                [
                    'label' => 'Pindah Kamar',
                    'route' => 'room-change.index',
                    'permission' => 'change_room',
                ],
                [
                    'label' => 'Issue Card',
                    'route' => 'issue-card.index',
                    'permission' => 'issue_card',
                ],
                [
                    'label' => 'Deposit Kartu',
                    'route' => 'deposits.index',
                    'permission' => 'checkin',
                ],
                [
                    'label' => 'Service Charge',
                    'route' => 'service-charge.index',
                    'permission' => 'checkin',
                ],
            ],
        ],

        // ─── Room Rack & Availability ────────────────────────────────
        [
            'label' => 'Availability',
            'icon' => 'bed',
            'children' => [
                [
                    'label' => 'Room Rack',
                    'route' => 'room-rack.index',
                    'permission' => 'view_rooms',
                ],
                [
                    'label' => 'Occupancy Calendar',
                    'route' => 'room-rack.occupancy',
                    'permission' => 'view_rooms',
                ],
            ],
        ],

        // ─── Booking ─────────────────────────────────────────────────
        [
            'label' => 'Booking',
            'icon' => 'calendar-plus',
            'children' => [
                [
                    'label' => 'Booking Single',
                    'route' => 'booking.create',
                    'permission' => 'create_booking',
                    'modal' => true,
                ],
                [
                    'label' => 'Booking OTA',
                    'route' => 'booking.ota-create',
                    'permission' => 'create_booking',
                    'modal' => true,
                ],
                [
                    'label' => 'Booking Group',
                    'route' => 'booking.group.create',
                    'permission' => 'create_booking_group',
                    'modal' => true,
                ],
            ],
        ],

        // ─── Room Management ─────────────────────────────────────────
        [
            'label' => 'Room Management',
            'icon' => 'bed',
            'children' => [
                [
                    'label' => 'Room List',
                    'route' => 'room-list.index',
                ],
                [
                    'label' => 'Rooms',
                    'route' => 'rooms.index',
                    'permission' => 'view_rooms',
                ],
                [
                    'label' => 'Room Types',
                    'route' => 'room-types.index',
                    'permission' => 'view_room_types',
                ],
                [
                    'label' => 'Promo Harga',
                    'route' => 'promo-prices.index',
                    'permission' => 'manage_promo_prices',
                ],

            ],
        ],

        // ─── Housekeeping ────────────────────────────────────────────
        [
            'label' => 'Housekeeping',
            'icon' => 'broom',
            'permission' => 'view_housekeeping',
            'children' => [
                [
                    'label' => 'Housekeeping',
                    'route' => 'housekeeping.index',
                ],
                [
                    'label' => 'Print Room List',
                    'route' => 'room-list.print',
                ],
            ],
        ],

        // ─── Guest Management ────────────────────────────────────────
        [
            'label' => 'Guest Management',
            'icon' => 'users',
            'children' => [
                [
                    'label' => 'Guest List',
                    'route' => 'guests.index',
                    'permission' => 'manage_guests',
                ],
            ],
        ],

        // ─── Pendapatan Resto ────────────────────────────────────────
        [
            'label' => 'Pendapatan Resto',
            'icon' => 'utensils',
            'route' => 'resto.index',
            'permission' => null,
            'roles' => ['owner', 'admin', 'frontoffice'],
        ],

        // ─── Night Audit v2 ─────────────────────────────────────────
        [
            'label' => 'Night Audit v2',
            'icon' => 'moon',
            'route' => 'reports.night-audit-v2.index',
            'permission' => 'view_reports',
            'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
        ],

        // ─── Pengeluaran ────────────────────────────────────────────
        [
            'label' => 'Pengeluaran',
            'icon' => 'money-bill-wave',
            'route' => 'expenses.index',
            'permission' => 'view_reports',
            'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
        ],

        // ─── Reports ─────────────────────────────────────────────────
        [
            'label' => 'Reports',
            'icon' => 'chart-bar',
            'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
            'children' => [
                [
                    'label' => 'Night Audit',
                    'route' => 'reports.night-audit',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Guest List Report',
                    'route' => 'reports.guest-list',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Occupancy',
                    'route' => 'reports.occupancy',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Revenue',
                    'route' => 'reports.revenue',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Reservation Report',
                    'route' => 'reports.reservations',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Group Report',
                    'route' => 'reports.group',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Laporan Pengeluaran',
                    'route' => 'reports.expenses',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
                [
                    'label' => 'Log Email OTA',
                    'route' => 'ota-email-logs.index',
                    'permission' => 'view_reports',
                    'roles' => ['owner', 'admin', 'frontoffice', 'user_manager'],
                ],
            ],
        ],

        // ─── Administration ──────────────────────────────────────────
        [
            'label' => 'Administration',
            'icon' => 'cog',
            'roles' => ['owner', 'admin'],
            'children' => [
                [
                    'label' => 'Permission Dashboard',
                    'route' => 'admin.permissions.dashboard',
                    'permission' => 'manage_users',
                ],
                [
                    'label' => 'Permissions',
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
                [
                    'label' => 'API Keys',
                    'route' => 'admin.api-keys',
                    'permission' => 'manage_users',
                ],
            ],
        ],

        // ─── Master Data ─────────────────────────────────────────────
        [
            'label' => 'Master Data',
            'icon' => 'database',
            'roles' => ['owner', 'admin'],
            'children' => [
                [
                    'label' => 'Metode Pembayaran',
                    'route' => 'admin.payment-methods.index',
                    'permission' => 'manage_users',
                ],
            ],
        ],

        // ─── Setting ─────────────────────────────────────────────────
        [
            'label' => 'Setting Hotel',
            'icon' => 'sliders-h',
            'route' => 'admin.settings',
            'roles' => ['owner', 'admin'],
            'permission' => 'manage_users',
        ],

        // ─── Tutorial ─────────────────────────────────────────────────
        [
            'label' => 'Tutorial',
            'icon' => 'book',
            'route' => 'help.index',
        ],

    ],
];
