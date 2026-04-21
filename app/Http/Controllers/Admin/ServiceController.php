<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function create(): View
    {
        return view('admin.services.add');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
        ]);

        Service::create([
            'category' => $validated['category'],
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.services.create')->with('success', 'Service added successfully.');
    }

    public function edit(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $services = Service::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.edit', compact('services', 'q'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'category' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->ignore((int) $request->input('service_id'))],
        ]);

        $service = Service::findOrFail((int) $validated['service_id']);
        $service->update([
            'category' => $validated['category'],
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.services.edit')->with('success', 'Service updated successfully.');
    }

    public function delete(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $services = Service::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.delete', compact('services', 'q'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        Service::whereKey((int) $validated['service_id'])->delete();

        return redirect()->route('admin.services.delete')->with('success', 'Service deleted successfully.');
    }
}
