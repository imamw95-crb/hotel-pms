<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Reservation;

class ReservationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Use policy for update permission
        return $this->user()->can('update', $this->route('reservation'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'guest_name'   => 'sometimes|string|max:255',
            'guest_phone'  => 'nullable|string|max:20',
            'guest_email'  => 'nullable|email|max:255',
            'check_in'     => 'sometimes|date_format:Y-m-d',
            'check_out'    => 'sometimes|date_format:Y-m-d|after:check_in',
            'guest_count'  => 'nullable|integer|min:1|max:10',
            'notes'        => 'nullable|string|max:1000',
            'payment_method'=> 'nullable|string|max:50',
        ];
    }
}
