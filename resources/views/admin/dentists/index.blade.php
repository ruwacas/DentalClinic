@extends('layouts.app', ['title' => 'Manage Dentist Account'])

@section('content')
<section class="card" id="dentist-account-card" data-failed-dialog-id="{{ old('edit_dentist_id') ? 'edit-dentist-'.old('edit_dentist_id') : '' }}">
    <h2>Manage Dentist Account</h2>
    <p>View, edit, and delete dentist accounts.</p>

    <div class="inline-form" style="justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <form method="GET" action="{{ route('admin.dentists.index') }}" class="inline-form" style="margin: 0;">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name, email, phone, specialty">
            <button class="btn btn-ghost" type="submit">Search</button>
            @if (!empty($q))
                <a class="btn btn-ghost" href="{{ route('admin.dentists.index') }}">Clear</a>
            @endif
        </form>

        <a class="btn" href="{{ route('admin.dentists.create') }}">+ Add Dentist</a>
    </div>

    <div class="dentist-table-wrap">
        <table class="dentist-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialty</th>
                    <th>Experience</th>
                    <th>Bio</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dentists as $dentist)
                    <tr>
                        <td>{{ $dentist->name }}</td>
                        <td>{{ $dentist->email }}</td>
                        <td>{{ $dentist->phone ?: '-' }}</td>
                        <td>{{ $dentist->dentistProfile?->specialty ?: '-' }}</td>
                        <td>{{ $dentist->dentistProfile?->years_of_experience ?? 0 }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($dentist->dentistProfile?->bio ?: '-', 45) }}</td>
                        <td class="dentist-actions-cell">
                            <button
                                class="btn btn-ghost btn-icon js-open-dentist-dialog"
                                type="button"
                                data-dialog-id="edit-dentist-{{ $dentist->id }}"
                                title="Update dentist account"
                            >
                                <span class="btn-icon-svg" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        <path d="m12 6 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Update</span>
                            </button>

                            <form method="POST" action="{{ route('admin.dentists.destroy', $dentist) }}" onsubmit="return confirm('Delete this dentist account?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-icon" type="submit" title="Delete dentist account">
                                    <span class="btn-icon-svg" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M5 7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M9 7V5h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="m8 7 1 12h6l1-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No dentist accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach ($dentists as $dentist)
        @php($isOldTarget = (string) old('edit_dentist_id') === (string) $dentist->id)
        <dialog class="dentist-edit-dialog" id="edit-dentist-{{ $dentist->id }}">
            <div class="dentist-edit-head">
                <h3>Update Dentist Account</h3>
                <button type="button" class="btn btn-ghost js-close-dentist-dialog">Close</button>
            </div>

            <form method="POST" action="{{ route('admin.dentists.update', $dentist) }}" class="grid-form dentist-edit-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_dentist_id" value="{{ $dentist->id }}">

                <label>Name
                    <input type="text" name="name" value="{{ $isOldTarget ? old('name', $dentist->name) : $dentist->name }}" required>
                </label>

                <label>Email
                    <input type="email" name="email" value="{{ $isOldTarget ? old('email', $dentist->email) : $dentist->email }}" required>
                </label>

                <label>Phone
                    <input type="text" name="phone" value="{{ $isOldTarget ? old('phone', $dentist->phone) : $dentist->phone }}">
                </label>

                <label>Specialty
                    <input type="text" name="specialty" value="{{ $isOldTarget ? old('specialty', $dentist->dentistProfile?->specialty) : $dentist->dentistProfile?->specialty }}">
                </label>

                <label>Years of Experience
                    <input type="number" name="years_of_experience" min="0" value="{{ $isOldTarget ? old('years_of_experience', $dentist->dentistProfile?->years_of_experience ?? 0) : ($dentist->dentistProfile?->years_of_experience ?? 0) }}">
                </label>

                <label>Bio
                    <textarea name="bio">{{ $isOldTarget ? old('bio', $dentist->dentistProfile?->bio) : $dentist->dentistProfile?->bio }}</textarea>
                </label>

                <label>New Password
                    <input type="password" name="password" placeholder="Leave blank to keep current password">
                </label>

                <div class="inline-form" style="justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost js-close-dentist-dialog">Cancel</button>
                    <button class="btn btn-icon" type="submit">
                        <span class="btn-icon-svg" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M5 4h11l3 3v13H5V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M8 4v6h8V4" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M8 16h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </dialog>
    @endforeach

    @if ($dentists->count() > 0)
        <div style="margin-top: 1rem;">
            {{ $dentists->links() }}
        </div>
    @endif
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openButtons = Array.from(document.querySelectorAll('.js-open-dentist-dialog'));
    const dentistAccountCard = document.getElementById('dentist-account-card');
    const failedDialogId = dentistAccountCard ? (dentistAccountCard.getAttribute('data-failed-dialog-id') || '') : '';

    if (!openButtons.length) {
        return;
    }

    const closeDialog = function (dialog) {
        if (dialog && typeof dialog.close === 'function') {
            dialog.close();
        }
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const dialogId = button.getAttribute('data-dialog-id');
            const dialog = dialogId ? document.getElementById(dialogId) : null;

            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
            }
        });
    });

    Array.from(document.querySelectorAll('.dentist-edit-dialog')).forEach((dialog) => {
        Array.from(dialog.querySelectorAll('.js-close-dentist-dialog')).forEach((closeButton) => {
            closeButton.addEventListener('click', function () {
                closeDialog(dialog);
            });
        });

        dialog.addEventListener('click', function (event) {
            const box = dialog.getBoundingClientRect();
            const inside = event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom;

            if (!inside) {
                closeDialog(dialog);
            }
        });
    });

    if (failedDialogId) {
        const failedDialog = document.getElementById(failedDialogId);
        if (failedDialog && typeof failedDialog.showModal === 'function') {
            failedDialog.showModal();
        }
    }
});
</script>
@endsection
