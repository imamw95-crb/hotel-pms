<?php

namespace App\Services;

use App\Models\HotelSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class PaymentDateService
{
    /**
     * Tanggal pembayaran yang dipilih staff adalah TANGGAL BISNIS, bukan timestamp
     * kalender. Periode bisnis = [tanggal cutoff, besok cutoff) — dengan cutoff
     * 06:00, jam 00:00–05:59 milik periode bisnis SEBELUMNYA.
     *
     * Karena itu timestamp hasil input harus digeser ke hari berikutnya kalau
     * jamnya masih sebelum cutoff. Tanpa ini, pembayaran yang diinput jam 02:30
     * dengan tanggal hari ini akan nyangkut di night audit kemarin (yang mungkin
     * sudah di-lock) dan tidak pernah muncul di periode yang dipilih.
     *
     * @param  CarbonInterface  $date       Tanggal yang dipilih di form.
     * @param  CarbonInterface|null  $reference  Sumber jam (default: sekarang).
     *                                           Untuk edit, pakai created_at lama
     *                                           supaya jam asli transaksi tetap.
     */
    public static function forBusinessDate(CarbonInterface $date, ?CarbonInterface $reference = null): Carbon
    {
        $reference = $reference ?? now();

        // Tanggal yang dipilih = tanggal kalender hari ini → uang diterima saat
        // ini juga, jadi pakai waktu sebenarnya (walau jamnya sebelum cutoff).
        if ($date->format('Y-m-d') === $reference->format('Y-m-d')) {
            return Carbon::parse($reference->format('Y-m-d H:i:s'));
        }

        $cutoff = self::cutoffTime();

        $timestamp = Carbon::parse($date->format('Y-m-d').' '.$reference->format('H:i:s'));
        $start = Carbon::parse($date->format('Y-m-d').' '.$cutoff);

        return $timestamp->lessThan($start) ? $timestamp->addDay() : $timestamp;
    }

    public static function cutoffTime(): string
    {
        $cutoff = HotelSetting::get()->cutoff_time ?? '06:00';

        return substr($cutoff, 0, 5);
    }
}
