@extends('layouts.app')

@section('title', 'Create ticket')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">New request</p>
            <h1>Create ticket</h1>
        </div>
        <a class="button button--quiet" href="{{ route('tickets.index') }}">Back to tickets</a>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" class="card form-stack">
        @csrf

        <div class="field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="150" value="{{ old('title') }}" required>
            @error('title')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="8" required>{{ old('description') }}</textarea>
            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
                @foreach (\App\Models\Ticket::PRIORITIES as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', \App\Models\Ticket::PRIORITY_NORMAL) === $priority)>
                        {{ ucfirst($priority) }}
                    </option>
                @endforeach
            </select>
            @error('priority')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button class="button" type="submit">Create ticket</button>
        </div>
    </form>
@endsection
