@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">{{ auth()->user()->organization->name }}</p>
            <h1>Tickets</h1>
        </div>
        <a class="button" href="{{ route('tickets.create') }}">Create ticket</a>
    </div>

    <form class="filters" method="GET" action="{{ route('tickets.index') }}">
        <div class="field filters__search">
            <label for="search">Search</label>
            <input
                id="search"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Title or description"
            >
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach (\App\Models\Ticket::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" @selected($status === $statusOption)>
                        {{ str($statusOption)->replace('_', ' ')->title() }}
                    </option>
                @endforeach
            </select>
        </div>
        <button class="button button--secondary" type="submit">Apply</button>
        @if ($search || $status)
            <a class="button button--quiet" href="{{ route('tickets.index') }}">Clear</a>
        @endif
    </form>

    @if ($tickets->isEmpty())
        <section class="empty-state">
            <h2>No tickets found</h2>
            <p>Try changing the filters or create a new ticket.</p>
        </section>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Status</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Creator</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->title }}</a></td>
                            <td><span class="badge">{{ str($ticket->status)->replace('_', ' ')->title() }}</span></td>
                            <td>{{ ucfirst($ticket->priority) }}</td>
                            <td>{{ $ticket->creator->name }}</td>
                            <td><time datetime="{{ $ticket->created_at->toIso8601String() }}">{{ $ticket->created_at->format('M j, Y g:i A') }}</time></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $tickets->links() }}
    @endif
@endsection
