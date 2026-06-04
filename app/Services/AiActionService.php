<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiActionService
{
    /**
     * Execute an action based on parsed intent from AI.
     *
     * @param array $action  {action: string, data: array}
     * @return array {success: bool, message: string, needs_confirmation?: bool, summary?: array}
     */
    public function execute(array $action): array
    {
        $method = match ($action['action']) {
            'create_booking' => 'actionCreateBooking',
            'checkin' => 'actionCheckin',
            'checkout' => 'actionCheckout',
            'payment' => 'actionPayment',
            'cancel' => 'actionCancel',
            'change_room' => 'actionChangeRoom',
            'search_guest' => 'actionSearchGuest',
            'deposit_create' => 'actionDepositCreate',
            'deposit_return' => 'actionDepositReturn',
            'housekeeping' => 'actionHousekeeping',
            'extend_stay' => 'actionExtendStay',
            'update_rate' => 'actionUpdateRate',
            'toggle_breakfast' => 'actionToggleBreakfast',
            default => null,
        };

        if (! $method || ! method_exists($this, $method)) {
            return [
                'success' => false,
                'message' => 'Aksi tidak dikenali. Silakan coba lagi.',
            ];
        }

        return $this->$method($action['data'] ?? []);
    }

    // ──────────────────────────────────────────────
    //  RESOLVE RESERVATION
    // ──────────────────────────────────────────────

    /**
     * Cari reservasi berdasarkan query (nomor reservasi, nama tamu, atau nomor kamar).
     *
     * @return array {reservation: Reservation|null, multiple: bool, matches: string}
     */
    public function resolveReservation(string $query): array
    {
        // 1. Coba cari berdasarkan nomor reservasi
        $reservation = Reservation::where('reservation_number', $query)
            ->with(['guest', 'room'])
            ->first();
        if ($reservation) {
            return ['reservation' => $reservation, 'multiple' => false, 'matches' => ''];
        }

        // 2. Coba cari berdasarkan nomor kamar
        $room = Room::where('room_number', $query)->first();
        if ($room) {
            $reservation = Reservation::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'checked_in'])
                ->with(['guest', 'room'])
                ->latest()
                ->first();
            if ($reservation) {
                return ['reservation' => $reservation, 'multiple' => false, 'matches' => ''];
            }
        }

        // 3. Coba cari berdasarkan nama tamu
        $guests = Guest::where('guest_name', 'like', "%{$query}%")->get();
        if ($guests->isNotEmpty()) {
            $reservations = Reservation::whereIn('guest_id', $guests->pluck('id'))
                ->whereIn('status', ['pending', 'checked_in', 'checked_out'])
                ->with(['guest', 'room'])
                ->latest()
                ->get();

            if ($reservations->count() === 1) {
                return ['reservation' => $reservations->first(), 'multiple' => false, 'matches' => ''];
            }

            if ($reservations->count() > 1) {
                $matches = $reservations->take(5)->map(fn ($r) => [
                    'label' => "{$r->reservation_number} - {$r->guest->guest_name} (Kamar {$r->room->room_number}) [{$r->status}]",
                    'id' => $r->id,
                ]);

                return [
                    'reservation' => null,
                    'multiple' => true,
                    'matches' => $reservations->take(5)->map(fn ($r) =>
                        "`{$r->reservation_number}` — {$r->guest->guest_name} (Kamar {$r->room->room_number}, {$r->status})"
                    )->implode("\n"),
                ];
            }
        }

        return ['reservation' => null, 'multiple' => false, 'matches' => ''];
    }

    /**
     * Format reservation info untuk ditampilkan ke user.
     */
    private function formatReservationInfo(Reservation $r): string
    {
        $fmt = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
        $ci = $r->check_in ? $r->check_in->format('d M Y') : '-';
        $co = $r->check_out ? $r->check_out->format('d M Y') : '-';

        return "📋 No: `{$r->reservation_number}`\n".
            "👤 Tamu: {$r->guest->guest_name}\n".
            "🛏️ Kamar {$r->room->room_number} ({$r->room->room_type_name})\n".
            "📅 Check-in: {$ci} (14:00) → Check-out: {$co} (12:00)\n".
            "💰 Total: {$fmt($r->total_amount)} | Dibayar: {$fmt($r->paid_amount)} | Sisa: {$fmt($r->remaining_payment)}\n".
            "📌 Status: **{$r->status_label}**";
    }

    // ──────────────────────────────────────────────
    //  ACTION: CREATE BOOKING (from AI natural language)
    // ──────────────────────────────────────────────

    public function actionCreateBooking(array $data): array
    {
        $today = Carbon::now()->startOfDay();

        try {
            $checkIn = Carbon::parse($data['checkin_date'])->setTime(14, 0);
            $checkOut = Carbon::parse($data['checkout_date'])->setTime(12, 0);
        } catch (\Exception $e) {
            return ['success' => true, 'message' => 'Format tanggal tidak valid.'];
        }

        if ($checkIn->gte($checkOut)) {
            return ['success' => true, 'message' => 'Check-out harus setelah check-in.'];
        }
        if ($checkIn->lt($today)) {
            return ['success' => true, 'message' => 'Check-in tidak boleh di masa lalu.'];
        }

        $roomTypeName = $data['room_type'] ?? null;
        $avail = Room::where('status', '!=', 'maintenance')
            ->whereNotIn('id', fn ($q) => $q->select('room_id')->from('reservations')->whereIn('status',['pending','checked_in'])->where('check_in','<',$checkOut)->where('check_out','>',$checkIn));

        $room = null;
        if ($roomTypeName) {
            $room = (clone $avail)->where('room_type_name', $roomTypeName)->orderBy('room_number')->first();
        }
        if (! $room) {
            $room = (clone $avail)->orderBy('room_number')->first();
        }
        if (! $room) {
            return ['success' => true, 'message' => 'Tidak ada kamar tersedia untuk tanggal tersebut.'];
        }

        try {
            $reservation = DB::transaction(function () use ($data, $room, $checkIn, $checkOut) {
                $guest = Guest::firstOrCreate(['guest_name' => $data['guest_name']], ['phone'=>null,'email'=>null,'address'=>null]);
                $total = (float)($data['total_price']??0) ?: $room->calculateTotalForRange($checkIn, $checkOut);

                return Reservation::create([
                    'guest_id' => $guest->id, 'room_id' => $room->id,
                    'check_in' => $checkIn, 'check_out' => $checkOut,
                    'number_of_cards' => $data['guest_count'] ?? 1,
                    'total_amount' => $total,
                    'payment_method' => $data['payment_method'] ?: 'cash',
                    'paid_amount' => 0, 'status' => 'pending',
                    'notes' => ($data['notes']??'').' (via AI Chat)',
                    'ota_source' => 'ai_chat', 'created_by' => auth()->id() ?? 1,
                ]);
            });

            $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
            return [
                'success' => true,
                'message' => '✅ Reservasi '.$reservation->reservation_number.' — '.$reservation->guest->guest_name
                    .' | '.$reservation->room->room_number.' ('.$reservation->room->room_type_name.')'
                    .' | CI: '.$reservation->check_in->format('d M').' CO: '.$reservation->check_out->format('d M')
                    .' | '.$fmt($reservation->total_amount).' — **Pending**',
            ];
        } catch (\Exception $e) {
            Log::error('AI Chat booking failed: '.$e->getMessage());
            return ['success' => true, 'message' => 'Gagal membuat reservasi. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: SEARCH GUEST / RESERVATION
    // ──────────────────────────────────────────────

    public function actionSearchGuest(array $data): array
    {
        $query = $data['query'] ?? '';
        if (empty($query)) {
            return [
                'success' => true,
                'message' => 'Silakan sebutkan nama tamu atau nomor reservasi yang ingin dicari.',
            ];
        }

        // Cari reservasi dulu
        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan yang mana.",
            ];
        }
        if ($resolved['reservation']) {
            return [
                'success' => true,
                'message' => "🔍 **Reservasi ditemukan!**\n\n".$this->formatReservationInfo($resolved['reservation']),
            ];
        }

        // Fallback: cari guest aja
        $guests = Guest::where('guest_name', 'like', "%{$query}%")->get();
        if ($guests->isNotEmpty()) {
            $list = $guests->take(5)->map(fn ($g) => "👤 {$g->guest_name}".($g->phone ? " ({$g->phone})" : ''))->implode("\n");

            return [
                'success' => true,
                'message' => "🔍 **Tamu ditemukan:**\n{$list}\n\nKetik nama untuk detail reservasi.",
            ];
        }

        return [
            'success' => true,
            'message' => "Tidak ditemukan reservasi atau tamu dengan kata kunci \"{$query}\". Coba dengan nomor reservasi (contoh: INV-001) atau nama lain.",
        ];
    }

    // ──────────────────────────────────────────────
    //  ACTION: CHECK-IN
    // ──────────────────────────────────────────────

    public function actionCheckin(array $data): array
    {
        $query = $data['query'] ?? '';
        $cardCount = (int) ($data['card_count'] ?? 1);

        if (empty($query)) {
            return [
                'success' => false,
                'message' => 'Silakan sebutkan nama tamu atau nomor reservasi untuk check-in.',
            ];
        }

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'needs_confirmation' => false,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan nomor reservasi yang akan di-check-in.",
            ];
        }

        $reservation = $resolved['reservation'];
        if (! $reservation) {
            return [
                'success' => false,
                'message' => "Reservasi atas \"{$query}\" tidak ditemukan. Pastikan nama atau nomor reservasi sudah benar.",
            ];
        }

        if ($reservation->status !== 'pending') {
            return ['success' => false, 'message' => "{$reservation->reservation_number} status **{$reservation->status_label}**, tidak bisa check-in."];
        }

        if (! ($data['confirmed'] ?? false)) {
            return [
                'success' => true, 'needs_confirmation' => true,
                'message' => 'Check-in: '.$this->formatReservationInfo($reservation)." | 🃏 {$cardCount} kartu\nKonfirmasi? (ya/tidak)",
                'summary' => ['action'=>'checkin','reservation_id'=>$reservation->id,'query'=>$query,'card_count'=>$cardCount,'reservation_number'=>$reservation->reservation_number],
            ];
        }

        try {
            return DB::transaction(function () use ($reservation, $cardCount) {
                $reservation->update(['status'=>'checked_in','number_of_cards'=>$cardCount]);
                $reservation->room->update(['status'=>'occupied']);
                Transaction::create(['reservation_id'=>$reservation->id,'type'=>'checkin_payment','amount'=>0,'payment_method'=>'cash','notes'=>'Check-in via AI Chat','created_by'=>auth()->id()??1]);
                $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
                return [
                    'success' => true,
                    'message' => '✅ Check-in: '.$reservation->reservation_number.' — '.$reservation->guest->guest_name
                        .' | '.$reservation->room->room_number.' ('.$reservation->room->room_type_name.') | 🃏 '.$cardCount
                        .' kartu | Sisa '.$fmt($reservation->remaining_payment),
                ];
            });
        } catch (\Exception $e) {
            Log::error('AI Chat checkin failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal check-in. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: CHECK-OUT
    // ──────────────────────────────────────────────

    public function actionCheckout(array $data): array
    {
        $query = $data['query'] ?? '';
        $amount = (float) ($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cash';

        if (empty($query)) {
            return [
                'success' => false,
                'message' => 'Silakan sebutkan nama tamu atau nomor kamar untuk check-out.',
            ];
        }

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'needs_confirmation' => false,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan nomor reservasi yang akan di-check-out.",
            ];
        }

        $reservation = $resolved['reservation'];
        if (! $reservation) {
            return [
                'success' => false,
                'message' => "Reservasi atas \"{$query}\" tidak ditemukan.",
            ];
        }

        if ($reservation->status !== 'checked_in') {
            return ['success'=>false, 'message'=>"{$reservation->reservation_number} status **{$reservation->status_label}**, tidak bisa check-out."];
        }

        $remaining = (float) $reservation->remaining_payment;
        if ($remaining > 0 && $amount <= 0 && ! ($data['confirmed'] ?? false)) {
            $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
            return [
                'success'=>true, 'needs_confirmation'=>true,
                'message'=> 'Check-out: '.$this->formatReservationInfo($reservation)." | ⚠️ Sisa {$fmt($remaining)} — sebutkan jumlah bayar (contoh: bayar {$fmt($remaining)} cash)",
                'summary'=>['action'=>'checkout','reservation_id'=>$reservation->id,'query'=>$query,'needs_payment'=>true,'remaining'=>$remaining],
            ];
        }

        if (! ($data['confirmed'] ?? false)) {
            return [
                'success'=>true, 'needs_confirmation'=>true,
                'message'=> 'Check-out: '.$this->formatReservationInfo($reservation)."\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'checkout','reservation_id'=>$reservation->id,'query'=>$query,'needs_payment'=>false],
            ];
        }

        try {
            return DB::transaction(function () use ($reservation, $amount, $paymentMethod) {
                if ($amount > 0) {
                    Transaction::create(['reservation_id'=>$reservation->id,'type'=>'checkout_payment','amount'=>$amount,'payment_method'=>$paymentMethod,'notes'=>'Check-out via AI Chat','created_by'=>auth()->id()??1]);
                    $reservation->increment('paid_amount', $amount);
                }
                $reservation->update(['status'=>'checked_out']);
                $reservation->room->update(['status'=>'cleaning']);
                HousekeepingTask::create(['room_id'=>$reservation->room_id,'task_type'=>'cleaning','priority'=>'normal','description'=>'Auto after check-out','status'=>'pending','created_by'=>auth()->id()??1]);

                $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
                $msg = '✅ Check-out: '.$reservation->reservation_number.' — '.$reservation->guest->guest_name.' | '.$reservation->room->room_number.' → Cleaning';
                if ($amount > 0) $msg .= ' | Bayar '.$fmt($amount).' ('.$paymentMethod.')';
                return ['success'=>true, 'message'=>$msg];
            });
        } catch (\Exception $e) {
            Log::error('AI Chat checkout failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal check-out. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: ADD PAYMENT
    // ──────────────────────────────────────────────

    public function actionPayment(array $data): array
    {
        $query = $data['query'] ?? '';
        $amount = (float) ($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $type = $data['type'] ?? 'dp';

        if (empty($query) || $amount <= 0) {
            return [
                'success' => false,
                'message' => 'Silakan sebutkan nama tamu/nomor reservasi dan jumlah pembayaran. Contoh: "Bayar Rp 300.000 untuk Budi"',
            ];
        }

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'needs_confirmation' => false,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan nomor reservasi yang akan dibayar.",
            ];
        }

        $reservation = $resolved['reservation'];
        if (! $reservation) {
            return [
                'success' => false,
                'message' => "Reservasi atas \"{$query}\" tidak ditemukan.",
            ];
        }

        if (in_array($reservation->status, ['cancelled','checked_out'])) {
            return ['success'=>false, 'message'=>"{$reservation->reservation_number} sudah {$reservation->status_label}, tidak bisa bayar."];
        }

        if (! ($data['confirmed'] ?? false)) {
            $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
            return [
                'success'=>true, 'needs_confirmation'=>true,
                'message'=> "Bayar {$fmt($amount)} ({$paymentMethod}) — {$reservation->guest->guest_name} ({$reservation->reservation_number})\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'payment','reservation_id'=>$reservation->id,'query'=>$query,'amount'=>$amount,'payment_method'=>$paymentMethod,'type'=>$type],
            ];
        }

        try {
            Transaction::create(['reservation_id'=>$reservation->id,'type'=>$type,'amount'=>$amount,'payment_method'=>$paymentMethod,'notes'=>'Pembayaran via AI Chat','created_by'=>auth()->id()??1]);
            $reservation->increment('paid_amount', $amount);
            $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');
            $remaining = (float) $reservation->fresh()->remaining_payment;
            return [
                'success'=>true,
                'message'=> '✅ Bayar '.$fmt($amount).' ('.$paymentMethod.') — '.$reservation->reservation_number.' | Total bayar '.$fmt($reservation->fresh()->paid_amount).' | Sisa '.$fmt($remaining),
            ];
        } catch (\Exception $e) {
            Log::error('AI Chat payment failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal bayar. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: CANCEL RESERVATION
    // ──────────────────────────────────────────────

    public function actionCancel(array $data): array
    {
        $query = $data['query'] ?? '';

        if (empty($query)) {
            return [
                'success' => false,
                'message' => 'Silakan sebutkan nomor reservasi yang akan dibatalkan.',
            ];
        }

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'needs_confirmation' => false,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan nomor reservasi yang akan dibatalkan.",
            ];
        }

        $reservation = $resolved['reservation'];
        if (! $reservation) {
            return [
                'success' => false,
                'message' => "Reservasi atas \"{$query}\" tidak ditemukan.",
            ];
        }

        if ($reservation->status === 'checked_in') {
            return ['success'=>false, 'message'=>"{$reservation->reservation_number} sudah check-in. Lakukan check-out dulu."];
        }
        if (in_array($reservation->status, ['cancelled','checked_out'])) {
            return ['success'=>false, 'message'=>"{$reservation->reservation_number} sudah {$reservation->status_label}."];
        }

        if (! ($data['confirmed'] ?? false)) {
            $paid = (float) $reservation->paid_amount;
            $msg = 'Cancel: '.$this->formatReservationInfo($reservation);
            if ($paid > 0) $msg .= ' | ⚠️ Sudah bayar '.$paid;
            return [
                'success'=>true, 'needs_confirmation'=>true,
                'message'=> $msg."\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'cancel','reservation_id'=>$reservation->id,'query'=>$query],
            ];
        }

        try {
            $reservation->update(['status'=>'cancelled']);
            return ['success'=>true, 'message'=>'✅ Cancel: '.$reservation->reservation_number.' — '.$reservation->guest->guest_name.' | **Cancelled**'];
        } catch (\Exception $e) {
            Log::error('AI Chat cancel failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal cancel. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: CHANGE ROOM
    // ──────────────────────────────────────────────

    public function actionChangeRoom(array $data): array
    {
        $query = $data['query'] ?? '';
        $newRoomNumber = $data['new_room_number'] ?? '';
        $reason = $data['reason'] ?? 'Pindah kamar via AI Chat';

        if (empty($query) || empty($newRoomNumber)) {
            return [
                'success' => false,
                'message' => 'Silakan sebutkan tamu dan nomor kamar baru. Contoh: "Pindahkan Budi ke kamar 205"',
            ];
        }

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) {
            return [
                'success' => true,
                'needs_confirmation' => false,
                'message' => "Ditemukan beberapa reservasi:\n{$resolved['matches']}\n\nSilakan sebutkan nomor reservasi yang akan dipindah.",
            ];
        }

        $reservation = $resolved['reservation'];
        if (! $reservation) {
            return [
                'success' => false,
                'message' => "Reservasi atas \"{$query}\" tidak ditemukan.",
            ];
        }

        if ($reservation->status !== 'checked_in') {
            return ['success'=>false, 'message'=>"Status {$reservation->status_label}, tidak bisa pindah kamar."];
        }

        $newRoom = Room::where('room_number', $newRoomNumber)->first();
        if (! $newRoom) return ['success'=>false, 'message'=>"Kamar {$newRoomNumber} tidak ditemukan."];
        if ($newRoom->id === $reservation->room_id) return ['success'=>false, 'message'=>"Sudah di kamar {$newRoomNumber}."];
        if ($newRoom->status !== 'available') return ['success'=>false, 'message'=>"Kamar {$newRoomNumber} sedang {$newRoom->status}."];

        if (! ($data['confirmed'] ?? false)) {
            return [
                'success'=>true, 'needs_confirmation'=>true,
                'message'=> "Pindah: {$reservation->guest->guest_name} dari {$reservation->room->room_number} → **{$newRoomNumber}**\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'change_room','reservation_id'=>$reservation->id,'query'=>$query,'new_room_id'=>$newRoom->id,'new_room_number'=>$newRoomNumber,'reason'=>$reason],
            ];
        }

        try {
            return DB::transaction(function () use ($reservation, $newRoom) {
                $oldRoom = $reservation->room;
                $reservation->update(['room_id'=>$newRoom->id]);
                $oldRoom->update(['status'=>'cleaning']);
                $newRoom->update(['status'=>'occupied']);
                return ['success'=>true, 'message'=>"✅ Pindah: {$reservation->guest->guest_name} {$oldRoom->room_number} → **{$newRoom->room_number}**"];
            });
        } catch (\Exception $e) {
            Log::error('AI Chat change room failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal pindah kamar. Coba manual.'];
        }
    }

    // ─── DEPOSIT CREATE ─────────────────────────

    public function actionDepositCreate(array $data): array
    {
        $query = $data['query'] ?? '';
        if (empty($query)) return ['success'=>false, 'message'=>'Sebutkan nama tamu.'];

        $cardCount = (int)($data['card_count']??1);
        $nominalPerCard = (float)($data['nominal_per_card']??50000);
        $paymentMethod = $data['payment_method']??'cash';

        $resolved = $this->resolveReservation($query);
        $guest = $resolved['reservation']?->guest;
        if (! $guest) {
            $guests = Guest::where('guest_name','like',"%{$query}%")->get();
            if ($guests->count()===1) $guest = $guests->first();
            elseif ($guests->count()>1) return ['success'=>true, 'message'=>"Beberapa tamu ditemukan: ".$guests->take(5)->pluck('guest_name')->implode(', ').". Sebutkan nama lengkap."];
            else return ['success'=>false, 'message'=>"Tamu '{$query}' tidak ditemukan."];
        }

        $total = $cardCount * $nominalPerCard;
        if (! ($data['confirmed'] ?? false)) {
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"Deposit: {$guest->guest_name} | {$cardCount} kartu × {$fmt($nominalPerCard)} = {$fmt($total)} ({$paymentMethod})\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'deposit_create','guest_id'=>$guest->id,'card_count'=>$cardCount,'nominal_per_card'=>$nominalPerCard,'payment_method'=>$paymentMethod],
            ];
        }

        try {
            $deposit = Deposit::create(['guest_id'=>$guest->id,'number_of_cards'=>$cardCount,'nominal_per_card'=>$nominalPerCard,'total_amount'=>$total,'payment_method'=>$paymentMethod,'notes'=>'Deposit via AI Chat','status'=>'active','created_by'=>auth()->id()??1]);
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return ['success'=>true, 'message'=>"✅ Deposit {$deposit->receipt_number} — {$guest->guest_name} | {$cardCount} kartu × {$fmt($nominalPerCard)} = {$fmt($total)} | **Active**"];
        } catch (\Exception $e) {
            Log::error('AI Chat deposit create failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal buat deposit. Coba manual.'];
        }
    }

    // ─── DEPOSIT RETURN ──────────────────────────

    public function actionDepositReturn(array $data): array
    {
        $receiptNumber = $data['receipt_number'] ?? '';
        $query = $data['query'] ?? '';

        $deposit = null;
        if ($receiptNumber) {
            $deposit = Deposit::where('receipt_number',$receiptNumber)->where('status','active')->with('guest')->first();
        }
        if (! $deposit && $query) {
            $resolved = $this->resolveReservation($query);
            if ($resolved['reservation']) {
                $deposit = Deposit::where('reservation_id',$resolved['reservation']->id)->where('status','active')->latest()->with('guest')->first();
            }
            if (! $deposit) {
                $guest = Guest::where('guest_name','like',"%{$query}%")->first();
                if ($guest) $deposit = Deposit::where('guest_id',$guest->id)->where('status','active')->latest()->with('guest')->first();
            }
        }
        if (! $deposit) return ['success'=>false, 'message'=>'Tidak ada deposit aktif.'];

        if (! ($data['confirmed'] ?? false)) {
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"Return {$deposit->receipt_number} — {$deposit->guest->guest_name} | {$fmt($deposit->total_amount)}\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'deposit_return','deposit_id'=>$deposit->id,'receipt_number'=>$deposit->receipt_number],
            ];
        }

        try {
            $deposit->update(['status'=>'returned']);
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return ['success'=>true, 'message'=>"✅ Return {$deposit->receipt_number} — {$deposit->guest->guest_name} | {$fmt($deposit->total_amount)} | **Returned**"];
        } catch (\Exception $e) {
            Log::error('AI Chat deposit return failed: '.$e->getMessage());
            return ['success'=>false, 'message'=>'Gagal return deposit. Coba manual.'];
        }
    }

    // ──────────────────────────────────────────────
    //  ACTION: HOUSEKEEPING TASK
    // ──────────────────────────────────────────────

    public function actionHousekeeping(array $data): array
    {
        $roomNumber = $data['room_number'] ?? $data['query'] ?? '';
        $taskType = in_array($data['task_type']??'',['cleaning','deep_clean','maintenance','inspection','turndown']) ? $data['task_type'] : 'cleaning';
        $priority = in_array($data['priority']??'',['low','normal','high','urgent']) ? $data['priority'] : 'normal';
        $description = $data['description'] ?? '';

        if (empty($roomNumber)) return ['success'=>false,'message'=>'Sebutkan nomor kamar.'];
        $room = Room::where('room_number',$roomNumber)->first();
        if (! $room) return ['success'=>false,'message'=>"Kamar {$roomNumber} tidak ditemukan."];

        if (! ($data['confirmed'] ?? false)) {
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"HK Task: {$roomNumber} — {$taskType} ({$priority})".($description ? " — {$description}" : '')."\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'housekeeping','room_id'=>$room->id,'room_number'=>$roomNumber,'task_type'=>$taskType,'priority'=>$priority,'description'=>$description],
            ];
        }

        try {
            HousekeepingTask::create(['room_id'=>$room->id,'task_type'=>$taskType,'priority'=>$priority,'description'=>$description?:'Task via AI Chat','status'=>'pending','created_by'=>auth()->id()??1]);
            return ['success'=>true,'message'=>"✅ HK Task: {$roomNumber} — {$taskType} ({$priority}) | **Pending**"];
        } catch (\Exception $e) {
            Log::error('AI Chat HK task failed: '.$e->getMessage());
            return ['success'=>false,'message'=>'Gagal buat task HK. Coba manual.'];
        }
    }

    // ─── EXTEND STAY ─────────────────────────────

    public function actionExtendStay(array $data): array
    {
        $query = $data['query'] ?? '';
        if (empty($query)) return ['success'=>false,'message'=>'Sebutkan nama/reservasi untuk perpanjangan.'];

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) return ['success'=>true,'message'=>"Ditemukan:\n{$resolved['matches']}\nSebutkan nomor reservasi."];

        $reservation = $resolved['reservation'];
        if (! $reservation) return ['success'=>false,'message'=>"Reservasi '{$query}' tidak ditemukan."];
        if (! in_array($reservation->status,['checked_in','pending'])) return ['success'=>false,'message'=>"{$reservation->status_label}, tidak bisa diperpanjang."];

        $additionalNights = (int)($data['additional_nights']??1);
        $newCheckOut = $data['new_checkout'] ?? null;
        $currentCO = $reservation->check_out->copy();

        try {
            $newCO = $newCheckOut ? Carbon::parse($newCheckOut)->setTime(12,0) : $currentCO->copy()->addDays($additionalNights);
        } catch (\Exception $e) {
            return ['success'=>false,'message'=>'Format tanggal tidak valid.'];
        }

        if ($newCO->lte($currentCO)) return ['success'=>false,'message'=>"Harus setelah {$currentCO->format('d M')}."];

        $room = $reservation->room;
        if ($room && !$room->isAvailable($reservation->check_in,$newCO,$reservation->id)) return ['success'=>false,'message'=>"Kamar {$room->room_number} tidak tersedia untuk tanggal tsb."];

        $additionalAmount = $room ? $room->calculateTotalForRange($currentCO, $newCO) : 0;
        $extNights = (int)$currentCO->startOfDay()->diffInDays($newCO->startOfDay());

        if (! ($data['confirmed'] ?? false)) {
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"Extend: {$reservation->guest->guest_name} | CO {$currentCO->format('d M')} → **{$newCO->format('d M')}** (+{$extNights} malam)".($additionalAmount>0 ? " | +{$fmt($additionalAmount)}" : '')."\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'extend_stay','reservation_id'=>$reservation->id,'new_checkout'=>$newCO->format('Y-m-d'),'additional_amount'=>$additionalAmount],
            ];
        }

        try {
            $reservation->update(['check_out'=>$newCO]);
            if ($additionalAmount > 0) $reservation->increment('total_amount', $additionalAmount);
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return ['success'=>true,'message'=>"✅ Extend: {$reservation->guest->guest_name} | CO → {$newCO->format('d M')}".($additionalAmount>0 ? " | +{$fmt($additionalAmount)}" : '')];
        } catch (\Exception $e) {
            Log::error('AI Chat extend failed: '.$e->getMessage());
            return ['success'=>false,'message'=>'Gagal extend. Coba manual.'];
        }
    }

    // ─── UPDATE RATE ─────────────────────────────

    public function actionUpdateRate(array $data): array
    {
        $query = $data['query'] ?? '';
        $newRate = (float)($data['new_rate']??0);
        if (empty($query) || $newRate<=0) return ['success'=>false,'message'=>'Sebutkan tamu dan tarif baru.'];

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) return ['success'=>true,'message'=>"Ditemukan:\n{$resolved['matches']}\nSebutkan nomor reservasi."];

        $reservation = $resolved['reservation'];
        if (! $reservation) return ['success'=>false,'message'=>"Reservasi '{$query}' tidak ditemukan."];
        if (in_array($reservation->status,['checked_out','cancelled'])) return ['success'=>false,'message'=>"{$reservation->status_label}, tidak bisa diupdate."];

        $nights = $reservation->nights;
        $newTotal = $newRate * $nights;

        if (! ($data['confirmed'] ?? false)) {
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"Rate: {$reservation->guest->guest_name} | {$fmt($reservation->room->price_per_night)} → **{$fmt($newRate)}**/malam | Total {$fmt($newTotal)}\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'update_rate','reservation_id'=>$reservation->id,'new_rate'=>$newRate,'new_total'=>$newTotal],
            ];
        }

        try {
            $reservation->update(['custom_room_rate'=>$newRate,'total_amount'=>$newTotal]);
            $fmt = fn($v)=>'Rp '.number_format($v,0,',','.');
            return ['success'=>true,'message'=>"✅ Rate: {$reservation->guest->guest_name} | **{$fmt($newRate)}**/malam | Total **{$fmt($newTotal)}**"];
        } catch (\Exception $e) {
            Log::error('AI Chat update rate failed: '.$e->getMessage());
            return ['success'=>false,'message'=>'Gagal update rate. Coba manual.'];
        }
    }

    // ─── TOGGLE BREAKFAST ────────────────────────

    public function actionToggleBreakfast(array $data): array
    {
        $query = $data['query'] ?? '';
        if (empty($query)) return ['success'=>false,'message'=>'Sebutkan nama/reservasi.'];

        $resolved = $this->resolveReservation($query);
        if ($resolved['multiple']) return ['success'=>true,'message'=>"Ditemukan:\n{$resolved['matches']}\nSebutkan nomor reservasi."];

        $reservation = $resolved['reservation'];
        if (! $reservation) return ['success'=>false,'message'=>"Reservasi '{$query}' tidak ditemukan."];
        if (in_array($reservation->status,['checked_out','cancelled'])) return ['success'=>false,'message'=>"{$reservation->status_label}."];

        $newValue = ! $reservation->include_breakfast;
        if (! ($data['confirmed'] ?? false)) {
            return [
                'success'=>true,'needs_confirmation'=>true,
                'message'=>"Breakfast: {$reservation->guest->guest_name} | Saat ini ".($reservation->include_breakfast?'✅':'❌')." → **".($newValue?'Include':'Remove')."**\nKonfirmasi? (ya/tidak)",
                'summary'=>['action'=>'toggle_breakfast','reservation_id'=>$reservation->id,'new_value'=>$newValue],
            ];
        }

        try {
            $reservation->update(['include_breakfast'=>$newValue]);
            return ['success'=>true,'message'=>'✅ Breakfast: '.$reservation->guest->guest_name.' | '.($newValue?'**Include Breakfast** 🌅':'**Remove Breakfast**')];
        } catch (\Exception $e) {
            Log::error('AI Chat toggle breakfast failed: '.$e->getMessage());
            return ['success'=>false,'message'=>'Gagal update sarapan. Coba manual.'];
        }
    }
}
