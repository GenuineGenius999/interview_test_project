<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->organization_id === $ticket->organization_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->isManager()
            && $user->organization_id === $ticket->organization_id;
    }
}
