<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Booking Permissions
            ['name' => 'Create Booking', 'slug' => 'create_booking', 'group' => 'booking', 'description' => 'Dapat membuat booking baru'],
            ['name' => 'View Bookings', 'slug' => 'view_bookings', 'group' => 'booking', 'description' => 'Dapat melihat daftar booking'],
            ['name' => 'Edit Booking', 'slug' => 'edit_booking', 'group' => 'booking', 'description' => 'Dapat mengubah booking'],
            ['name' => 'Delete Booking', 'slug' => 'delete_booking', 'group' => 'booking', 'description' => 'Dapat menghapus booking'],
            ['name' => 'Create Booking Group', 'slug' => 'create_booking_group', 'group' => 'booking', 'description' => 'Dapat membuat booking group'],

            // Reservation Permissions
            ['name' => 'View Reservations', 'slug' => 'view_reservations', 'group' => 'reservation', 'description' => 'Dapat melihat daftar reservasi'],
            ['name' => 'Edit Reservation', 'slug' => 'edit_reservation', 'group' => 'reservation', 'description' => 'Dapat mengubah reservasi'],
            ['name' => 'Cancel Reservation', 'slug' => 'cancel_reservation', 'group' => 'reservation', 'description' => 'Dapat membatalkan reservasi'],
            ['name' => 'Add Payment', 'slug' => 'add_payment', 'group' => 'reservation', 'description' => 'Dapat menambah pembayaran'],

            // Check-in/Check-out Permissions
            ['name' => 'Check In', 'slug' => 'checkin', 'group' => 'checkin', 'description' => 'Dapat melakukan check-in'],
            ['name' => 'Check Out', 'slug' => 'checkout', 'group' => 'checkin', 'description' => 'Dapat melakukan check-out'],
            ['name' => 'Issue Card', 'slug' => 'issue_card', 'group' => 'checkin', 'description' => 'Dapat issue kartu kamar'],
            ['name' => 'Re-issue Card', 'slug' => 'reissue_card', 'group' => 'checkin', 'description' => 'Dapat re-issue kartu kamar'],

            // Room Permissions
            ['name' => 'View Rooms', 'slug' => 'view_rooms', 'group' => 'room', 'description' => 'Dapat melihat daftar kamar'],
            ['name' => 'Create Room', 'slug' => 'create_room', 'group' => 'room', 'description' => 'Dapat membuat kamar'],
            ['name' => 'Edit Room', 'slug' => 'edit_room', 'group' => 'room', 'description' => 'Dapat mengubah kamar'],
            ['name' => 'Delete Room', 'slug' => 'delete_room', 'group' => 'room', 'description' => 'Dapat menghapus kamar'],
            ['name' => 'View Room Dashboard', 'slug' => 'view_room_dashboard', 'group' => 'room', 'description' => 'Dapat melihat room dashboard'],
            ['name' => 'Manage Rooms', 'slug' => 'manage_rooms', 'group' => 'room', 'description' => 'Dapat mengelola semua kamar'],
            ['name' => 'Pindah Kamar', 'slug' => 'change_room', 'group' => 'room', 'description' => 'Dapat memindahkan tamu ke kamar lain'],

            // Room Type Permissions
            ['name' => 'View Room Types', 'slug' => 'view_room_types', 'group' => 'room', 'description' => 'Dapat melihat tipe kamar'],
            ['name' => 'Create Room Type', 'slug' => 'create_room_type', 'group' => 'room', 'description' => 'Dapat membuat tipe kamar'],
            ['name' => 'Edit Room Type', 'slug' => 'edit_room_type', 'group' => 'room', 'description' => 'Dapat mengubah tipe kamar'],
            ['name' => 'Delete Room Type', 'slug' => 'delete_room_type', 'group' => 'room', 'description' => 'Dapat menghapus tipe kamar'],

            // Report Permissions
            ['name' => 'View Reports', 'slug' => 'view_reports', 'group' => 'report', 'description' => 'Dapat melihat laporan'],
            ['name' => 'Export Reports', 'slug' => 'export_reports', 'group' => 'report', 'description' => 'Dapat export laporan'],

            // User Permissions
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'group' => 'user', 'description' => 'Dapat mengelola pengguna'],
            ['name' => 'Create User', 'slug' => 'create_user', 'group' => 'user', 'description' => 'Dapat membuat pengguna'],
            ['name' => 'Edit User', 'slug' => 'edit_user', 'group' => 'user', 'description' => 'Dapat mengubah pengguna'],
            ['name' => 'Delete User', 'slug' => 'delete_user', 'group' => 'user', 'description' => 'Dapat menghapus pengguna'],

            // Guest Permissions
            ['name' => 'Manage Guests', 'slug' => 'manage_guests', 'group' => 'guest', 'description' => 'Dapat mengelola data tamu'],

            // Service Charge Permissions
            ['name' => 'View Service Charges', 'slug' => 'view_service_charges', 'group' => 'service', 'description' => 'Dapat melihat service charge'],
            ['name' => 'Create Service Charge', 'slug' => 'create_service_charge', 'group' => 'service', 'description' => 'Dapat membuat service charge'],

            // Housekeeping Permissions
            ['name' => 'View Housekeeping', 'slug' => 'view_housekeeping', 'group' => 'housekeeping', 'description' => 'Dapat melihat tugas housekeeping'],
            ['name' => 'Create Housekeeping Task', 'slug' => 'create_housekeeping_task', 'group' => 'housekeeping', 'description' => 'Dapat membuat tugas housekeeping'],
            ['name' => 'Update Housekeeping Status', 'slug' => 'update_housekeeping_status', 'group' => 'housekeeping', 'description' => 'Dapat mengubah status tugas housekeeping'],
            ['name' => 'Assign Housekeeping Task', 'slug' => 'assign_housekeeping_task', 'group' => 'housekeeping', 'description' => 'Dapat menugaskan staff housekeeping'],
            ['name' => 'Delete Housekeeping Task', 'slug' => 'delete_housekeeping_task', 'group' => 'housekeeping', 'description' => 'Dapat menghapus tugas housekeeping'],
            ['name' => 'Manage Lost & Found', 'slug' => 'manage_lost_found', 'group' => 'housekeeping', 'description' => 'Dapat mengelola barang temuan'],
            ['name' => 'Manage Housekeeping Inventory', 'slug' => 'manage_hk_inventory', 'group' => 'housekeeping', 'description' => 'Dapat mengelola inventaris housekeeping'],

            // Promo Pricing Permissions
            ['name' => 'Manage Promo Prices', 'slug' => 'manage_promo_prices', 'group' => 'pricing', 'description' => 'Dapat mengelola harga promo per tanggal'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignPermissionToRole('admin', [
            'create_booking', 'view_bookings', 'edit_booking', 'delete_booking', 'create_booking_group',
            'view_reservations', 'edit_reservation', 'cancel_reservation', 'add_payment',
            'checkin', 'checkout', 'issue_card', 'reissue_card',
            'view_rooms', 'create_room', 'edit_room', 'delete_room', 'view_room_dashboard', 'manage_rooms', 'change_room',
            'view_room_types', 'create_room_type', 'edit_room_type', 'delete_room_type',
            'view_reports', 'manage_guests',
            'view_service_charges', 'create_service_charge',
            'view_housekeeping', 'create_housekeeping_task', 'update_housekeeping_status', 'assign_housekeeping_task', 'delete_housekeeping_task',
            'manage_lost_found', 'manage_hk_inventory',
            'manage_promo_prices',
        ]);

        $this->assignPermissionToRole('frontoffice', [
            'create_booking', 'view_bookings', 'create_booking_group',
            'view_reservations', 'add_payment',
            'checkin', 'checkout', 'issue_card', 'reissue_card',
            'view_room_dashboard', 'change_room',
            'view_reports',
            'view_rooms', 'view_room_types', 'manage_guests',
            'view_service_charges', 'create_service_charge',
            'view_housekeeping', 'create_housekeeping_task', 'update_housekeeping_status',
            'manage_promo_prices',
        ]);

        $this->assignPermissionToRole('housekeeping', [
            'view_housekeeping', 'create_housekeeping_task', 'update_housekeeping_status', 'assign_housekeeping_task', 'delete_housekeeping_task',
            'manage_lost_found',
            'manage_guests',
        ]);

        $this->assignPermissionToRole('user_manager', [
            'manage_users',
            'create_user',
            'edit_user',
            'delete_user',
            'view_reports',
            'manage_promo_prices',
        ]);

        $this->assignPermissionToRole('owner', [
            'view_reports', 'export_reports',
            'manage_promo_prices',
            'manage_users', 'create_user', 'edit_user', 'delete_user',
        ]);
    }

    /**
     * Assign permissions to role
     */
    private function assignPermissionToRole($role, $permissionSlugs)
    {
        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');

        foreach ($permissionIds as $permissionId) {
            \DB::table('role_permission')->updateOrInsert(
                ['role' => $role, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
