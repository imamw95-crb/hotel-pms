<?php

namespace App\Services;

use App\Models\NightAuditLog;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class NightAuditLockService
{
    /**
     * Tanggal bisnis dari sebuah timestamp, mengikuti cutoff night audit.
     * Contoh cutoff 06:00: 2026-11-09 02:30 → periode bisnis 2026-11-08.
     */
    public static function businessDateOf(CarbonInterface $at): string
    {
        $cutoff = PaymentDateService::cutoffTime();
        $start = Carbon::parse($at->format('Y-m-d').' '.$cutoff);

        return $at->lessThan($start) ? $at->copy()->subDay()->format('Y-m-d') : $at->format('Y-m-d');
    }

    /**
     * Apakah periode bisnis dari timestamp tersebut sudah di-lock?
     *
     * Laporan yang sudah locked bersifat final (dibaca dari snapshot), jadi
     * koreksi untuk periode itu TIDAK boleh mengubah transaksi lamanya —
     * koreksi dicatat sebagai transaksi baru di periode berjalan.
     */
    public static function isLocked(?CarbonInterface $at): bool
    {
        if (! $at) {
            return false;
        }

        return NightAuditLog::whereDate('audit_date', self::businessDateOf($at))
            ->where('status', 'locked')
            ->exists();
    }

    /**
     * Periode bisnis yang sedang berjalan (belum di-lock) — dipakai sebagai
     * tanggal transaksi koreksi.
     */
    public static function currentOpenTimestamp(): Carbon
    {
        return Carbon::now();
    }
}
