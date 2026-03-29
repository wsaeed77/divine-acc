<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task->client);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('view', $task->client);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('view', $task->client);
    }
}
