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
            'label'      => 'Dashboard',
            'icon'       => 'tachometer-alt',
            'route'      => 'dashboard',
            'permission' => null,
        ],

        // ─── Front Desk ──────────────────────────────────────────────
        [
            'label'  => 'Front Desk',
            'icon'   => 'concierge-bell',
            'children' => [
                [
                    'label'      => 'Reservasi',
                    'route'      => 'reservations.index',
                    'permission' => 'view_reservations',
                ],
                [
                    'label'      => 'Check-in',
                    'route'      => 'checkin.index',
                    'permission' => 'checkin',
                ],
                [
                    'label'      => 'Issue Card',
                    'route'      => 'issue-card.index',
                    'permission' => 'issue_card',
                ],
                [
                    'label'      => 'Deposit Kartu',
                    'route'      => 'deposits.index',
                    'permission' => 'checkin',
                ],
            ],
        ],

        // ─── Booking ─────────────────────────────────────────────────
        [
            'label'  => 'Booking',
            'icon'   => 'calendar-plus',
            'children' => [
                [
                    'label'      => 'Booking Single',
                    'route'      => 'booking.create',
                    'permission' => 'create_booking',
                ],
                [
                    'label'      => 'Booking Group',
                    'route'      => 'booking.group.create',
                    'permission' => 'create_booking_group',
                ],
            ],
        ],

        // ─── Room Management ─────────────────────────────────────────
        [
            'label'  => 'Room Management',
            'icon'   => 'bed',
            'children' => [
                [
                    'label'      => 'Rooms',
                    'route'      => 'rooms.index',
                    'permission' => 'view_rooms',
                ],
                [
                    'label'      => 'Room Types',
                    'route'      => 'room-types.index',
                    'permission' => 'view_room_types',
                ],
                [
                    'label'      => 'Room Dashboard',
                    'route'      => 'rooms.dashboard',
                    'permission' => 'view_rooms',
                ],
            ],
        ],

        // ─── Guest Management ────────────────────────────────────────
        [
            'label'  => 'Guest Management',
            'icon'   => 'users',
            'children' => [
                [
                    'label'      => 'Guest List',
                    'route'      => 'guests.index',
                    'permission' => 'manage_guests',
                ],
            ],
        ],

        // ─── Reports ─────────────────────────────────────────────────
        [
            'label'  => 'Reports',
            'icon'   => 'chart-bar',
            'children' => [
                [
                    'label'      => 'Night Audit',
                    'route'      => 'reports.night-audit',
                    'permission' => 'view_reports',
                ],
                [
                    'label'      => 'Guest List Report',
                    'route'      => 'reports.guest-list',
                    'permission' => 'view_reports',
                ],
                [
                    'label'      => 'Occupancy',
                    'route'      => 'reports.occupancy',
                    'permission' => 'view_reports',
                ],
                [
                    'label'      => 'Revenue',
                    'route'      => 'reports.revenue',
                    'permission' => 'view_reports',
                ],
                [
                    'label'      => 'Reservation Report',
                    'route'      => 'reports.reservations',
                    'permission' => 'view_reports',
                ],
            ],
        ],

        // ─── Administration ──────────────────────────────────────────
        [
            'label'  => 'Administration',
            'icon'   => 'cog',
            'children' => [
                [
                    'label'      => 'Permission Dashboard',
                    'route'      => 'admin.permissions.dashboard',
                    'permission' => 'manage_users',
                ],
                [
                    'label'      => 'Permissions',
                    'route'      => 'admin.permissions.index',
                    'permission' => 'manage_users',
                ],
                [
                    'label'      => 'User Permissions',
                    'route'      => 'admin.permissions.user-permissions',
                    'permission' => 'manage_users',
                ],
                [
                    'label'      => 'Manage Users',
                    'route'      => 'admin.users.index',
                    'permission' => 'manage_users',
                ],
                [
                    'label'      => 'Backup Database',
                    'route'      => 'admin.backups.index',
                    'permission' => 'manage_users',
                ],
            ],
        ],

        // ─── Setting ─────────────────────────────────────────────────
        [
            'label'      => 'Setting Hotel',
            'icon'       => 'sliders-h',
            'route'      => 'admin.settings',
            'permission' => 'manage_users',
        ],

    ],
];
