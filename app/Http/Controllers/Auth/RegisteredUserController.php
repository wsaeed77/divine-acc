<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firm_name' => ['required', 'string', 'max:255'],
            'firm_email' => ['nullable', 'string', 'email', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $tenant = Tenant::query()->create([
            'name' => $validated['firm_name'],
            'slug' => Tenant::uniqueSlugFromName($validated['firm_name']),
            'email' => $validated['firm_email'] ?? null,
        ]);

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['owner_name'],
            'email' => Str::lower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_TENANT_ADMIN,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
