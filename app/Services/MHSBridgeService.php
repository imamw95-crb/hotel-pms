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
        $this->bridgeUrl = env('MHS_BRIDGE_URL', 'http://192.168.88.2:8080/bridge_api.php');
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
            'created_by' => auth()->id(),
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
            'created_by' => auth()->id(),
            'request_data' => compact('room'),
            'response_data' => $result,
            'success' => $result['success'] ?? false,
        ]);

        return $result;
    }

    public function eraseCard($room, $reservationId = null)
    {
        $response = Http::timeout($this->timeout)
            ->get($this->bridgeUrl, [
                'action' => 'erase_card',
                'room' => $room,
            ]);

        $result = $response->json();

        MHSLog::create([
            'command' => 'erase_card',
            'reservation_id' => $reservationId,
            'created_by' => auth()->id(),
            'request_data' => compact('room'),
            'response_data' => $result,
            'success' => $result['success'] ?? false,
        ]);

        return $result;
    }

    public function readCard()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->bridgeUrl, [
                    'action' => 'read',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Server MHS merespon dengan kode: '.$response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server MHS: '.$e->getMessage(),
            ];
        }
    }

    public function testConnection()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->bridgeUrl, [
                    'action' => 'test',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'connected' => false,
                'message' => 'Server MHS merespon dengan kode: '.$response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'connected' => false,
                'message' => 'Gagal terhubung ke server MHS: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Daftarkan encoder ke sistem MHS
     */
    public function registerEncoder($encoderIp = null, $encoderId = '01')
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->bridgeUrl, [
                    'action' => 'register_encoder',
                    'ip' => $encoderIp ?? '192.168.88.2',
                    'encoder_id' => $encoderId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Server MHS merespon dengan kode: '.$response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server MHS: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Ambil daftar kamar dari MHS
     */
    public function getRooms()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->bridgeUrl, [
                    'action' => 'rooms',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Server MHS merespon dengan kode: '.$response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server MHS: '.$e->getMessage(),
            ];
        }
    }
}
