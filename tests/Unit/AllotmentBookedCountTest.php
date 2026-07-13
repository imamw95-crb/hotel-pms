<?php

namespace Tests\Unit;

use App\Models\Allotment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AllotmentBookedCountTest extends TestCase
{
    public function test_increment_and_decrement_booked_only_count_nights_before_checkout_day(): void
    {
        DB::connection()->getSchemaBuilder()->create('allotments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('room_type_id');
            $table->date('date');
            $table->integer('allotment')->default(0);
            $table->integer('booked')->default(0);
            $table->string('channel')->nullable();
            $table->timestamps();
        });

        $roomTypeId = 77;
        $checkIn = Carbon::parse('2026-07-10 14:00');
        $checkOut = Carbon::parse('2026-07-11 12:00');

        Allotment::create([
            'room_type_id' => $roomTypeId,
            'date' => $checkIn->format('Y-m-d'),
            'allotment' => 10,
            'booked' => 0,
            'channel' => 'api',
        ]);

        Allotment::create([
            'room_type_id' => $roomTypeId,
            'date' => $checkOut->format('Y-m-d'),
            'allotment' => 10,
            'booked' => 0,
            'channel' => 'api',
        ]);

        Allotment::incrementBooked($roomTypeId, $checkIn, $checkOut, 'api');

        $this->assertSame(1, Allotment::where('room_type_id', $roomTypeId)
            ->whereDate('date', $checkIn->format('Y-m-d'))
            ->value('booked'));
        $this->assertSame(0, Allotment::where('room_type_id', $roomTypeId)
            ->whereDate('date', $checkOut->format('Y-m-d'))
            ->value('booked'));

        Allotment::decrementBooked($roomTypeId, $checkIn, $checkOut, 'api');

        $this->assertSame(0, Allotment::where('room_type_id', $roomTypeId)
            ->whereDate('date', $checkIn->format('Y-m-d'))
            ->value('booked'));
        $this->assertSame(0, Allotment::where('room_type_id', $roomTypeId)
            ->whereDate('date', $checkOut->format('Y-m-d'))
            ->value('booked'));
    }
}
