<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenant = $user->tenant()->with(['users' => fn ($q) => $q->orderBy('name')])->firstOrFail();

        return Inertia::render('Settings/Index', [
            'canManage' => $user->isTenantAdmin(),
            'firm' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'primary_color' => $tenant->primary_color ?? '#0f766e',
                'logo_url' => $tenant->logo_url,
            ],
            'team' => $tenant->users->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_current' => $u->id === $user->id,
            ]),
            'roleOptions' => [
                ['value' => User::ROLE_TENANT_ADMIN, 'label' => 'Tenant admin'],
                ['value' => User::ROLE_PARTNER, 'label' => 'Partner'],
                ['value' => User::ROLE_MANAGER, 'label' => 'Manager'],
                ['value' => User::ROLE_STAFF, 'label' => 'Staff'],
            ],
        ]);
    }

    public function updateFirm(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isTenantAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:5000'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $tenant = $user->tenant;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos/tenant-'.$tenant->id, 'public');
            $tenant->logo_path = $path;
        }

        $tenant->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'primary_color' => $validated['primary_color'] ?? $tenant->primary_color,
        ]);
        $tenant->save();

        return redirect()->route('settings.index')->with('success', 'Firm profile updated.');
    }

    public function storeTeamMember(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isTenantAdmin(), 403);

        $request->merge(['email' => Str::lower($request->input('email', ''))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in([
                User::ROLE_TENANT_ADMIN,
                User::ROLE_PARTNER,
                User::ROLE_MANAGER,
                User::ROLE_STAFF,
            ])],
        ]);

        User::query()->create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('settings.index')->with('success', 'Team member added.');
    }

    public function destroyTeamMember(Request $request, User $teamMember): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isTenantAdmin(), 403);

        if ($teamMember->id === $user->id) {
            return redirect()->route('settings.index')->with('error', 'You cannot remove yourself from the team.');
        }

        if ($teamMember->isTenantAdmin()) {
            $admins = User::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('role', User::ROLE_TENANT_ADMIN)
                ->count();

            if ($admins <= 1) {
                return redirect()->route('settings.index')->with('error', 'You cannot remove the last tenant admin.');
            }
        }

        $teamMember->delete();

        return redirect()->route('settings.index')->with('success', 'Team member removed.');
    }
}
