<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', Rule::in(Ticket::STATUSES)],
        ]);

        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        $tickets = Ticket::query()
            ->with('creator:id,name')
            ->where('organization_id', $request->user()->organization_id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'search', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        return view('tickets.create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = new Ticket($request->validated());
        $ticket->organization_id = $request->user()->organization_id;
        $ticket->created_by = $request->user()->id;
        $ticket->status = Ticket::STATUS_OPEN;
        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Request $request, $ticket): View
    {
        $this->authorize('view', $ticket);
        $ticket->load('creator:id,name');

        return view('tickets.show', compact('ticket'));
    }

    public function updateStatus(
        UpdateTicketStatusRequest $request,
        $ticket
    ): RedirectResponse {
        $ticket->update($request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }
}
