@extends('layouts.app', ['title' => 'Update Service'])

@section('content')
<section class="card">
    <h2>Update Service</h2>
    <p>Select a service and rename it.</p>

    <form method="GET" action="{{ route('admin.services.edit') }}" class="inline-form" style="margin-bottom: 1rem;">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search by category or service name">
        <button class="btn btn-ghost" type="submit">Search</button>
        @if (!empty($q))
            <a class="btn btn-ghost" href="{{ route('admin.services.edit') }}">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.services.update') }}" class="grid-form auth-form">
        @csrf
        @method('PUT')

        <label>Select Service
            <select name="service_id" id="service-id" required>
                <option value="">Choose service</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" data-name="{{ $service->name }}" data-category="{{ $service->category }}">{{ $service->category }} - {{ $service->name }}</option>
                @endforeach
            </select>
        </label>

        <label>Category
            <input type="text" name="category" id="service-category" value="{{ old('category') }}" required>
        </label>

        <label>New Service Name
            <input type="text" name="name" id="service-name" value="{{ old('name') }}" required>
        </label>

        <div class="inline-form">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">Back</a>
            <button class="btn" type="submit">Update Service</button>
        </div>
    </form>

    @if ($services->count() > 0)
        <div style="margin-top: 1rem;">
            {{ $services->links() }}
        </div>
    @endif
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('service-id');
    const input = document.getElementById('service-name');
    const categoryInput = document.getElementById('service-category');

    if (!select || !input || !categoryInput || input.value.trim() !== '' || categoryInput.value.trim() !== '') {
        return;
    }

    select.addEventListener('change', function () {
        const selected = select.options[select.selectedIndex];
        input.value = selected?.dataset?.name || '';
        categoryInput.value = selected?.dataset?.category || '';
    });
});
</script>
@endsection
