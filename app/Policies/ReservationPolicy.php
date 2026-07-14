<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any reservations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_reservations');
    }

    /**
     * Determine whether the user can view a reservation.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission('view_reservation');
    }

    /**
     * Determine whether the user can create reservations.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_reservation');
    }

    /**
     * Determine whether the user can update the reservation.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission('update_reservation');
    }

    /**
     * Determine whether the user can delete the reservation.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission('delete_reservation');
    }
}
