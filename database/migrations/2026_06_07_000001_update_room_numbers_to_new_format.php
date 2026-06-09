<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapping room number lama -> baru
     * Lantai 1: 0101-0112 (skip 0104)
     * Lantai 2: 0201-0211 (skip 0204)
     * Lantai 3: 0301-0311 (skip 0304)
     */
    private array $mapping = [
        '101' => '0101',
        '102' => '0102',
        '103' => '0103',
        '105' => '0105',
        '106' => '0106',
        '107' => '0107',
        '108' => '0108',
        '109' => '0109',
        '110' => '0110',
        '111' => '0111',
        '112' => '0112',
        '201' => '0201',
        '202' => '0202',
        '203' => '0203',
        '205' => '0205',
        '206' => '0206',
        '207' => '0207',
        '208' => '0208',
        '209' => '0209',
        '210' => '0210',
        '211' => '0211',
        '301' => '0301',
        '302' => '0302',
        '303' => '0303',
        '305' => '0305',
        '306' => '0306',
        '307' => '0307',
        '308' => '0308',
        '309' => '0309',
        '310' => '0310',
        '311' => '0311',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->mapping as $oldNumber => $newNumber) {
            DB::table('rooms')
                ->where('room_number', $oldNumber)
                ->update(['room_number' => $newNumber]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->mapping as $oldNumber => $newNumber) {
            DB::table('rooms')
                ->where('room_number', $newNumber)
                ->update(['room_number' => $oldNumber]);
        }
    }
};
