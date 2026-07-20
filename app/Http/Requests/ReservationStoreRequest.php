<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Reservation;

class ReservationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Use policy for create permission
        return $this->user()->can('create', Reservation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'guest_name'               => 'required|string|max:255',
            'guest_phone'              => 'nullable|string|max:20',
            'guest_email'              => 'nullable|email|max:255',
            'guest_id_number'          => 'nullable|string|max:50',
            'room_id'                  => 'required|exists:rooms,id',
            'check_in'                 => 'required|date_format:Y-m-d|after_or_equal:today',
            'check_out'                => 'required|date_format:Y-m-d|after:check_in',
            'guest_count'              => 'nullable|integer|min:1|max:10',
            'total_amount'             => 'nullable|numeric|min:0',
            'payment_method'           => 'nullable|string|max:50',
            'notes'                    => 'nullable|string|max:1000',
            'ota_source'               => 'nullable|string|max:50',
            'ota_reservation_number'   => 'nullable|string|max:100',
        ];
    }
}
