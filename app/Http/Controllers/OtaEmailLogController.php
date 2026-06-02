<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBookingEmailJob;
use App\Models\ProcessedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OtaEmailLogController extends Controller
{
    /**
     * Display a paginated list of processed OTA emails with filters.
     */
    public function index(Request $request)
    {
        $query = ProcessedEmail::query();

        // Filters — only apply when value is not empty
        if ($status = $request->status) {
            $query->whereStatus($status);
        }
        if ($otaSource = $request->ota_source) {
            $query->whereOta($otaSource);
        }
        if ($emailType = $request->email_type) {
            $query->whereEmailType($emailType);
        }
        $query->whereDateRange($request->date_from, $request->date_to);
        if ($search = $request->search) {
            $query->search($search);
        }

        // Sorting
        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->direction === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $logs = $query->paginate(25)->withQueryString();

        // Stats for summary cards
        $stats = ProcessedEmail::getStats();

        // Filter options
        $otaSources = ProcessedEmail::whereNotNull('ota_source')
            ->selectRaw('ota_source, COUNT(*) as total')
            ->groupBy('ota_source')
            ->orderByDesc('total')
            ->pluck('total', 'ota_source');

        $emailTypes = ProcessedEmail::whereNotNull('email_type')
            ->selectRaw('email_type, COUNT(*) as total')
            ->groupBy('email_type')
            ->orderByDesc('total')
            ->pluck('total', 'email_type');

        // Service monitoring status
        $serviceStatus = ProcessedEmail::getServiceStatus();

        return view('ota-email-logs.index', compact(
            'logs', 'stats', 'otaSources', 'emailTypes', 'serviceStatus'
        ));
    }

    /**
     * Show detailed info for a single processed email.
     */
    public function show(int $id)
    {
        $log = ProcessedEmail::findOrFail($id);

        return view('ota-email-logs.show', compact('log'));
    }

    /**
     * Refresh stats (clear cache).
     */
    public function refreshStats()
    {
        ProcessedEmail::clearStatsCache();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'stats' => ProcessedEmail::getStats(),
            ]);
        }

        return redirect()->route('ota-email-logs.index')
            ->with('success', 'Statistik berhasil diperbarui.');
    }

    /**
     * Refresh service monitoring status (clear cache).
     */
    public function refreshServiceStatus()
    {
        ProcessedEmail::clearServiceStatusCache();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'service_status' => ProcessedEmail::getServiceStatus(),
            ]);
        }

        return redirect()->route('ota-email-logs.index')
            ->with('success', 'Status service berhasil diperbarui.');
    }

    /**
     * Retry processing a failed email.
     * Re-dispatches the ProcessBookingEmailJob if possible.
     */
    public function retry(int $id)
    {
        $log = ProcessedEmail::findOrFail($id);

        if ($log->status !== 'failed') {
            return redirect()->route('ota-email-logs.show', $id)
                ->with('error', 'Hanya email dengan status Gagal yang bisa di-retry.');
        }

        if (empty($log->raw_body)) {
            return redirect()->route('ota-email-logs.show', $id)
                ->with('error', 'Tidak bisa retry: body email tidak tersimpan.');
        }

        try {
            // Re-dispatch the job
            ProcessBookingEmailJob::dispatch(
                emailUid: $log->email_uid.'_retry_'.time(),
                sender: $log->sender,
                subject: $log->subject ?? '',
                body: $log->raw_body,
                otaSource: $log->ota_source ?? 'unknown',
                emailType: $log->email_type ?? 'unknown',
            );

            Log::info('OTA email retry dispatched', [
                'original_id' => $log->id,
                'email_uid' => $log->email_uid,
                'sender' => $log->sender,
            ]);

            return redirect()->route('ota-email-logs.show', $id)
                ->with('success', 'Email sedang diproses ulang. Silakan cek log beberapa saat lagi.');
        } catch (\Exception $e) {
            Log::error('Failed to retry OTA email', [
                'id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('ota-email-logs.show', $id)
                ->with('error', 'Gagal memproses ulang: '.$e->getMessage());
        }
    }

    /**
     * API endpoint: return stats in JSON format.
     */
    public function apiStats()
    {
        return response()->json([
            'success' => true,
            'stats' => ProcessedEmail::getStats(),
        ]);
    }

    /**
     * API endpoint: return recent logs in JSON format.
     */
    public function apiRecent(Request $request)
    {
        $limit = min((int) $request->limit, 50);

        $logs = ProcessedEmail::latest()
            ->limit($limit)
            ->get(['id', 'subject', 'sender', 'status', 'ota_source', 'email_type', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
