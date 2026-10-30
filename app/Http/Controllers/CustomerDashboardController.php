<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders()
            ->with('items.product')
            ->latest()
            ->paginate(5);

        return view('dashboard', compact('user', 'orders'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'required_without:phone',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'required_without:email',
                'string',
                'regex:/^\+?[0-9\s().-]{7,24}$/',
                Rule::unique('users', 'phone')
                    ->ignore($user->id)
                    ->where(fn ($query) => $query->where('phone', preg_replace('/[^\d+]/', '', trim((string) $request->input('phone'))))),
            ],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->fill($validated)->save();

        return redirect()->route('dashboard')->with('success', 'Vos informations ont été mises à jour.');
    }
}
