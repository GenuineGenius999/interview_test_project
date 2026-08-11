<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantTicket
{
    public function handle(Request $request, Closure $next): Response
    {
        $ticketId = $request->route('ticket');

        if (! is_scalar($ticketId) || ! ctype_digit((string) $ticketId)) {
            abort(404);
        }

        $ticket = Ticket::query()
            ->where('organization_id', $request->user()->organization_id)
            ->findOrFail((int) $ticketId);

        $request->route()->setParameter('ticket', $ticket);

        return $next($request);
    }
}
