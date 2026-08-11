<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            && ($this->user()?->can('updateStatus', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Ticket::STATUSES)],
        ];
    }
}
