<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DentistProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DentistAccountController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $dentists = User::query()
            ->where('role', 'dentist')
            ->with('dentistProfile')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhereHas('dentistProfile', function ($profileQuery) use ($q) {
                            $profileQuery->where('specialty', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.dentists.index', [
            'dentists' => $dentists,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.dentists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'years_of_experience' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $dentist = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'dentist',
        ]);

        DentistProfile::updateOrCreate(
            ['user_id' => $dentist->id],
            [
                'specialty' => $validated['specialty'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? 0,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        return redirect()->route('admin.dentists.index')->with('success', 'Dentist account added successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'dentist', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'years_of_experience' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'dentist',
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        DentistProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialty' => $validated['specialty'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? 0,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        return back()->with('success', 'Dentist account updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === 'dentist', 404);

        $user->delete();

        return back()->with('success', 'Dentist account deleted.');
    }
}
