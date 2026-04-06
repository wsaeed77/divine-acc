<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $base = Task::query()
            ->with(['client', 'assignee'])
            ->whereHas('client', fn ($q) => $q->forTenant($tenantId));

        if ($user->role === User::ROLE_STAFF) {
            $base->whereHas('client', fn ($q) => $q->where('created_by_id', $user->id));
        }

        $overdueQuery = (clone $base)
            ->visibleInList()
            ->whereNotNull('deadline_date')
            ->whereDate('deadline_date', '<', now()->toDateString());

        $overdueCount = (clone $overdueQuery)->count();

        $overdueTasks = (clone $overdueQuery)
            ->orderBy('deadline_date')
            ->orderBy('id')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'overdueCount' => $overdueCount,
            'overdueTasks' => $overdueTasks,
        ]);
    }
}
