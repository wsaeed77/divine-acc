<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->tenant_id !== $client->tenant_id) {
            return false;
        }

        if ($user->role === User::ROLE_STAFF && (int) $client->created_by_id !== (int) $user->id) {
            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function update(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        if ($user->tenant_id !== $client->tenant_id) {
            return false;
        }

        return $user->isTenantAdmin();
    }
}
