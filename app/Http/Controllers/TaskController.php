<?php

namespace App\Http\Controllers;

use App\Models\ActionStatus;
use App\Models\BreakdownTemplate;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $query = Task::query()
            ->with(['client', 'assignee', 'latestAction', 'taskType'])
            ->whereHas('client', fn ($q) => $q->forTenant($tenantId));

        if ($user->role === User::ROLE_STAFF) {
            $query->whereHas('client', fn ($q) => $q->where('created_by_id', $user->id));
        }

        $status = $request->input('status', 'open');
        if ($status === 'completed') {
            $query->where('status', 'completed');
        } else {
            $query->visibleInList();
        }

        $tasks = $query
            ->orderByRaw('deadline_date IS NULL')
            ->orderBy('deadline_date')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function show(Task $task): RedirectResponse
    {
        return redirect()->route('tasks.edit', $task);
    }

    public function edit(Request $request, Task $task): Response
    {
        $task->load([
            'client',
            'taskType',
            'assignee',
            'monitor',
            'notifyUser',
            'latestAction',
            'breakdownTemplate',
            'breakdownItems',
        ]);

        $tenantId = $request->user()->tenant_id;

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'actionStatuses' => ActionStatus::query()->orderBy('name')->get(['id', 'name']),
            'breakdownTemplates' => BreakdownTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'task_type_id']),
            'userOptions' => User::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $userRule = Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId));

        $validated = $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'assignee_id' => ['nullable', 'integer', $userRule],
            'monitor_id' => ['nullable', 'integer', $userRule],
            'notify_user_id' => ['nullable', 'integer', $userRule],
            'latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'latest_action_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'target_date_manual' => ['boolean'],
            'deadline_date' => ['nullable', 'date'],
            'time_estimate' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'progress_notes' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'breakdown_template_id' => ['nullable', 'exists:breakdown_templates,id'],
            'is_favourite' => ['boolean'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.edit', $task)->with('success', 'Task saved.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function complete(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $request->user()->id,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task marked complete.');
    }
}
