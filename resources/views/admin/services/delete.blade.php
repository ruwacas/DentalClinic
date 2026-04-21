@extends('layouts.app', ['title' => 'Delete Service'])

@section('content')
<section class="card">
    <h2>Delete Service</h2>
    <p>Select a service to remove.</p>

    <form method="GET" action="{{ route('admin.services.delete') }}" class="inline-form" style="margin-bottom: 1rem;">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search by category or service name">
        <button class="btn btn-ghost" type="submit">Search</button>
        @if (!empty($q))
            <a class="btn btn-ghost" href="{{ route('admin.services.delete') }}">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.services.destroy') }}" class="grid-form auth-form" onsubmit="return confirm('Delete this service?');">
        @csrf
        @method('DELETE')

        <label>Select Service
            <select name="service_id" required>
                <option value="">Choose service</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->category }} - {{ $service->name }}</option>
                @endforeach
            </select>
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Back</a>
            <button class="btn btn-danger" type="submit">Delete Service</button>
        </div>
    </form>

    @if ($services->count() > 0)
        <div style="margin-top: 1rem;">
            {{ $services->links() }}
        </div>
    @endif
</section>
@endsection
