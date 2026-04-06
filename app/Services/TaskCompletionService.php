<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskCompletionService
{
    public function __construct(
        private ClientTaskSyncService $clientTaskSyncService
    ) {}

    /**
     * Mark task completed, roll forward relevant client compliance dates (recurring types),
     * then sync tasks so the next period task is created when applicable.
     */
    public function complete(Task $task, User $user): void
    {
        if ($task->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($task, $user) {
            $task->loadMissing(['taskType', 'client']);
            $client = $task->client;

            $client->load([
                'accountsReturn',
                'confirmationStatement',
                'vatDetail.vatFrequency',
                'payeDetail.payeFrequency',
                'cisDetail',
                'p11dDetail',
            ]);

            $this->rollComplianceDates($task, $client);

            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => $user->id,
            ]);

            $this->clientTaskSyncService->syncForClient($client->fresh());
        });
    }

    private function rollComplianceDates(Task $task, Client $client): void
    {
        $slug = $task->taskType?->slug;
        if ($slug === null) {
            return;
        }

        match ($slug) {
            'accounts_preparation', 'bookkeeping', 'management_accounts' => $this->rollAccountsPeriodEnd($client),
            'ch_submission' => $this->rollAccountsField($client, 'ch_accounts_next_due'),
            'ct600_submission' => $this->rollAccountsField($client, 'ct600_due'),
            'confirmation_statement' => $this->rollConfirmationStatement($client),
            'vat_submission', 'vat_preparation' => $this->rollVat($client),
            'paye' => $this->rollPaye($client),
            'cis' => $this->rollCis($client),
            'p11d' => $this->rollP11d($client),
            'auto_enrolment', 'self_assessment', 'pension' => null,
            default => null,
        };
    }

    private function rollAccountsPeriodEnd(Client $client): void
    {
        $ar = $client->accountsReturn;
        if (! $ar || $ar->accounts_period_end === null) {
            return;
        }

        $ar->accounts_period_end = $ar->accounts_period_end->copy()->addYear();
        $ar->save();
    }

    private function rollAccountsField(Client $client, string $field): void
    {
        $ar = $client->accountsReturn;
        if (! $ar || $ar->{$field} === null) {
            return;
        }

        $ar->{$field} = $ar->{$field}->copy()->addYear();
        $ar->save();
    }

    private function rollConfirmationStatement(Client $client): void
    {
        $cs = $client->confirmationStatement;
        if (! $cs || $cs->statement_due === null) {
            return;
        }

        $cs->statement_due = $cs->statement_due->copy()->addYear();
        $cs->save();
    }

    private function rollVat(Client $client): void
    {
        $vat = $client->vatDetail;
        if (! $vat) {
            return;
        }

        $months = $this->vatStepMonths($client);
        if ($vat->vat_period_end !== null) {
            $vat->vat_period_end = $vat->vat_period_end->copy()->addMonths($months);
        }
        if ($vat->next_return_due !== null) {
            $vat->next_return_due = $vat->next_return_due->copy()->addMonths($months);
        }
        $vat->save();
    }

    private function vatStepMonths(Client $client): int
    {
        $name = strtolower($client->vatDetail?->vatFrequency?->name ?? '');

        if (str_contains($name, 'month')) {
            return 1;
        }
        if (str_contains($name, 'annual')) {
            return 12;
        }

        return 3;
    }

    private function rollPaye(Client $client): void
    {
        $paye = $client->payeDetail;
        if (! $paye || $paye->rti_deadline === null) {
            return;
        }

        $name = $paye->payeFrequency?->name ?? 'Monthly';
        $base = $paye->rti_deadline->copy();

        $paye->rti_deadline = match ($name) {
            'Weekly' => $base->copy()->addWeek(),
            'Fortnightly' => $base->copy()->addWeeks(2),
            'Four-Weekly' => $base->copy()->addWeeks(4),
            default => $base->copy()->addMonth(),
        };
        $paye->save();
    }

    private function rollCis(Client $client): void
    {
        $cis = $client->cisDetail;
        if (! $cis) {
            return;
        }

        if ($cis->cis_date !== null) {
            $cis->cis_date = $cis->cis_date->copy()->addMonth();
        }
        if ($cis->cis_deadline !== null) {
            $cis->cis_deadline = $cis->cis_deadline->copy()->addMonth();
        }
        $cis->save();
    }

    private function rollP11d(Client $client): void
    {
        $p = $client->p11dDetail;
        if (! $p || $p->next_return_due === null) {
            return;
        }

        $p->next_return_due = $p->next_return_due->copy()->addYear();
        $p->save();
    }
}
