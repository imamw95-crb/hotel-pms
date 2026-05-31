<?php

namespace App\Services;

use App\Models\MHSLog;
use Illuminate\Support\Facades\Http;

class MHSBridgeService
{
    protected $bridgeUrl;

    protected $timeout;

    public function __construct()
    {
        $this->bridgeUrl = env('MHS_BRIDGE_URL', 'http://100.98.230.92/bridge_api.php');
        $this->timeout = 30;
    }

    public function checkin($room, $name, $checkin, $checkout, $reservationId = null)
    {
        $response = Http::timeout($this->timeout)
            ->get($this->bridgeUrl, [
                'action' => 'checkin',
                'room' => $room,
                'name' => $name,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]);

        $result = $response->json();

        MHSLog::create([
            'command' => 'checkin',
            'reservation_id' => $reservationId,
            'request_data' => compact('room', 'name', 'checkin', 'checkout'),
            'response_data' => $result,
            'success' => $result['success'] ?? false,
        ]);

        return $result;
    }

    public function checkout($room, $reservationId = null)
    {
        $response = Http::timeout($this->timeout)
            ->get($this->bridgeUrl, [
                'action' => 'checkout',
                'room' => $room,
            ]);

        $result = $response->json();

        MHSLog::create([
            'command' => 'checkout',
            'reservation_id' => $reservationId,
            'request_data' => compact('room'),
            'response_data' => $result,
            'success' => $result['success'] ?? false,
        ]);

        return $result;
    }

    public function readCard()
    {
        $response = Http::timeout($this->timeout)
            ->get($this->bridgeUrl, [
                'action' => 'read',
            ]);

        return $response->json();
    }

    public function testConnection()
    {
        $response = Http::timeout($this->timeout)
            ->get($this->bridgeUrl, [
                'action' => 'test',
            ]);

        return $response->json();
    }
}
