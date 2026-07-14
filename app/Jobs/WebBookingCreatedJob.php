<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\BookingNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebBookingCreatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reservation;
    protected $otaSource;

    /**
     * Create a new job instance.
     */
    public function __construct(Reservation $reservation, $otaSource = null)
    {
        $this->reservation = $reservation;
        $this->otaSource = $otaSource;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $service = app(BookingNotificationService::class);
        if ($this->otaSource && ! in_array($this->otaSource, ['website', 'api'])) {
            $service->otaBookingCreated(
                $this->reservation,
                [
                    'guest_name' => $this->reservation->guest->guest_name,
                    'reservation_id' => $this->reservation->ota_reservation_number ?? '',
                ],
                $this->otaSource
            );
        } else {
            $service->webBookingCreated($this->reservation);
        }
    }
}
