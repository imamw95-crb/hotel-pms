<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed permissions first
        $this->call(PermissionSeeder::class);
        // Users
        User::create(['name' => 'Owner Hotel', 'email' => 'owner@hotel.com', 'password' => Hash::make('password'), 'role' => 'owner']);
        User::create(['name' => 'Admin Hotel', 'email' => 'admin@hotel.com', 'password' => Hash::make('password'), 'role' => 'admin']);
        User::create(['name' => 'Front Office Staff', 'email' => 'frontoffice@hotel.com', 'password' => Hash::make('password'), 'role' => 'frontoffice']);

        // Room Types
        $roomTypes = [
            ['code' => '0001', 'name' => 'EXECUTIVE GARDEN VIEW', 'sequence' => 1],
            ['code' => '0002', 'name' => 'DELUXE', 'sequence' => 2],
            ['code' => '0003', 'name' => 'JUNIOR SUITE', 'sequence' => 3],
            ['code' => '0004', 'name' => 'FAMILY ROOM', 'sequence' => 4],
            ['code' => '0005', 'name' => 'DELUXE TWIN BED', 'sequence' => 5],
            ['code' => '0006', 'name' => 'PRESIDEN SUITE', 'sequence' => 6],
            ['code' => '0007', 'name' => 'SUPERIOR ROOM', 'sequence' => 7],
        ];
        foreach ($roomTypes as $type) {
            RoomType::create($type);
        }

        // Rooms
        $rooms = [
            ['0101', '0001', 500000, 2], ['0102', '0001', 500000, 2], ['0103', '0001', 500000, 2],
            ['0105', '0001', 500000, 2], ['0106', '0004', 750000, 4], ['0107', '0004', 750000, 4],
            ['0108', '0006', 1500000, 2], ['0109', '0007', 450000, 2], ['0110', '0007', 450000, 2],
            ['0111', '0007', 450000, 2], ['0112', '0007', 450000, 2], ['0201', '0003', 1200000, 2],
            ['0202', '0003', 1200000, 2], ['0203', '0003', 1200000, 2], ['0205', '0003', 1200000, 2],
            ['0206', '0003', 1200000, 2], ['0207', '0005', 800000, 2], ['0208', '0005', 800000, 2],
            ['0209', '0002', 650000, 2], ['0210', '0005', 800000, 2], ['0211', '0003', 1200000, 2],
            ['0301', '0003', 1200000, 2], ['0302', '0003', 1200000, 2], ['0303', '0003', 1200000, 2],
            ['0305', '0003', 1200000, 2], ['0306', '0003', 1200000, 2], ['0307', '0005', 800000, 2],
            ['0308', '0005', 800000, 2], ['0309', '0002', 650000, 2], ['0310', '0005', 800000, 2],
            ['0311', '0003', 1200000, 2],
        ];
        foreach ($rooms as $room) {
            Room::create([
                'room_number' => $room[0],
                'room_type_id' => RoomType::where('code', $room[1])->first()->id,
                'room_type_name' => RoomType::where('code', $room[1])->first()->name,
                'price_per_night' => $room[2],
                'max_occupancy' => $room[3],
                'status' => 'available',
            ]);
        }
    }
}