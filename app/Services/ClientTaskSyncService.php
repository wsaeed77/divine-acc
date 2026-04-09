<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientService;
use App\Models\ClientType;
use App\Models\Task;
use App\Models\TaskType;
use Carbon\Carbon;

class ClientTaskSyncService
{
    /**
     * Create / reactivate / switch off tasks when client services change (FR-TM-01, FR-TM-10).
     */
    public function syncForClient(Client $client): void
    {
        if ($client->is_prospect) {
            return;
        }

        $client->load([
            'clientType',
            'accountsReturn',
            'confirmationStatement',
            'vatDetail.vatFrequency',
            'payeDetail.payeFrequency',
            'cisDetail',
            'autoEnrolment',
            'p11dDetail',
        ]);

        $isSelfAssessment = $client->clientType?->name === ClientType::NAME_SELF_ASSESSMENT;

        $enabledIds = ClientService::query()
            ->where('client_id', $client->id)
            ->where('is_enabled', true)
            ->pluck('service_id');

        $disabledIds = ClientService::query()
            ->where('client_id', $client->id)
            ->where('is_enabled', false)
            ->pluck('service_id');

        if ($disabledIds->isNotEmpty()) {
            Task::query()
                ->where('client_id', $client->id)
                ->whereIn('service_id', $disabledIds)
                ->where('status', 'active')
                ->update(['status' => 'switched_off']);
        }

        if ($enabledIds->isNotEmpty()) {
            Task::query()
                ->where('client_id', $client->id)
                ->whereIn('service_id', $enabledIds)
                ->where('status', 'switched_off')
                ->update(['status' => 'active']);
        }

        $taskTypes = TaskType::query()
            ->where('is_active', true)
            ->whereNotNull('service_id')
            ->whereIn('service_id', $enabledIds)
            ->orderBy('display_order')
            ->get();

        foreach ($taskTypes as $type) {
            if ($isSelfAssessment && $type->slug === 'ch_submission') {
                continue;
            }

            if ($type->recurrence === 'one_off') {
                $alreadyExists = Task::query()
                    ->where('client_id', $client->id)
                    ->where('task_type_id', $type->id)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }
            }

            $hasActive = Task::query()
                ->where('client_id', $client->id)
                ->where('task_type_id', $type->id)
                ->where('status', 'active')
                ->exists();

            if ($hasActive) {
                continue;
            }

            $periodDate = $this->resolvePeriodDate($type, $client);
            $deadline = $type->deadline_manual ? null : $this->resolveDeadline($type, $client);
            $taskName = $this->buildTaskName($type, $client, $periodDate ?? $deadline);

            Task::query()->create([
                'client_id' => $client->id,
                'task_type_id' => $type->id,
                'service_id' => $type->service_id,
                'task_name' => $taskName,
                'status' => 'active',
                'assignee_id' => $client->manager_id,
                'period_date' => $periodDate,
                'deadline_date' => $deadline,
            ]);
        }
    }

    private function buildTaskName(TaskType $type, Client $client, ?Carbon $periodDate): string
    {
        $pattern = $type->naming_pattern;
        $formatted = $periodDate ? $periodDate->format('d/m/Y') : 'TBC';

        $pattern = str_replace('{dd/mm/yyyy}', $formatted, $pattern);
        $pattern = str_replace('{date}', $formatted, $pattern);

        if ($client->relationLoaded('vatDetail') && $client->vatDetail?->vatFrequency) {
            $pattern = str_replace('{Frequency}', $client->vatDetail->vatFrequency->name, $pattern);
        } else {
            $pattern = str_replace('{Frequency}', '—', $pattern);
        }

        if ($client->relationLoaded('payeDetail') && $client->payeDetail?->payeFrequency) {
            $pattern = str_replace('{PayeFrequency}', $client->payeDetail->payeFrequency->name, $pattern);
        } else {
            $pattern = str_replace('{PayeFrequency}', '—', $pattern);
        }

        $pattern = str_replace('{Month Year}', now()->format('F Y'), $pattern);
        $pattern = str_replace('{period}', now()->format('M Y'), $pattern);
        $pattern = str_replace('{tax year}', now()->format('Y').'/'.now()->addYear()->format('y'), $pattern);

        return $pattern;
    }

    private function resolvePeriodDate(TaskType $type, Client $client): ?Carbon
    {
        if ($type->slug === 'ch_submission') {
            return $this->resolveColumn($client, 'accounts_returns', 'accounts_period_end');
        }

        if (in_array($type->slug, ['vat_submission', 'vat_preparation'], true)) {
            return $this->resolveColumn($client, 'vat_details', 'vat_period_end');
        }

        if ($type->deadline_manual) {
            return null;
        }

        return $this->resolveDeadline($type, $client);
    }

    private function resolveDeadline(TaskType $type, Client $client): ?Carbon
    {
        if ($type->deadline_manual || ! $type->deadline_source) {
            return null;
        }

        $path = $type->deadline_source;
        if (! str_contains($path, '.')) {
            return null;
        }

        [$relationTable, $column] = explode('.', $path, 2);

        return $this->resolveColumn($client, $relationTable, $column);
    }

    private function resolveColumn(Client $client, string $relationTable, string $column): ?Carbon
    {
        $map = [
            'accounts_returns' => 'accountsReturn',
            'confirmation_statements' => 'confirmationStatement',
            'vat_details' => 'vatDetail',
            'paye_details' => 'payeDetail',
            'cis_details' => 'cisDetail',
            'auto_enrolment' => 'autoEnrolment',
            'p11d_details' => 'p11dDetail',
        ];

        $rel = $map[$relationTable] ?? null;
        if (! $rel || ! $client->$rel) {
            return null;
        }

        $value = $client->$rel->$column ?? null;

        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::parse($value->format('Y-m-d'));
        }

        return Carbon::parse((string) $value);
    }
}
