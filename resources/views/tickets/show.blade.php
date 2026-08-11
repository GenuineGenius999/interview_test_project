@extends('layouts.app')

@section('title', $ticket->title)

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Ticket #{{ $ticket->id }}</p>
            <h1>{{ $ticket->title }}</h1>
        </div>
        <a class="button button--quiet" href="{{ route('tickets.index') }}">Back to tickets</a>
    </div>

    <div class="detail-grid">
        <article class="card ticket-detail">
            <dl class="metadata">
                <div>
                    <dt>Status</dt>
                    <dd>{{ str($ticket->status)->replace('_', ' ')->title() }}</dd>
                </div>
                <div>
                    <dt>Priority</dt>
                    <dd>{{ ucfirst($ticket->priority) }}</dd>
                </div>
                <div>
                    <dt>Creator</dt>
                    <dd>{{ $ticket->creator->name }}</dd>
                </div>
                <div>
                    <dt>Created</dt>
                    <dd>{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt>Updated</dt>
                    <dd>{{ $ticket->updated_at->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>

            <section aria-labelledby="description-heading">
                <h2 id="description-heading">Description</h2>
                <div class="description">{{ $ticket->description }}</div>
            </section>
        </article>

        @can('updateStatus', $ticket)
            <aside class="card status-card">
                <h2>Update status</h2>
                <form method="POST" action="{{ route('tickets.status.update', $ticket) }}" class="form-stack">
                    @csrf
                    @method('PATCH')
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            @foreach (\App\Models\Ticket::STATUSES as $status)
                                <option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>
                                    {{ str($status)->replace('_', ' ')->title() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="button" type="submit">Update status</button>
                </form>
            </aside>
        @endcan
    </div>
@endsection
