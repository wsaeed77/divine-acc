<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompaniesHouseCompanyPreviewController extends Controller
{
    /**
     * Company profile + active directors for the CH search modal (preview step).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'company_number' => ['required', 'string', 'max:20'],
        ]);

        $apiKey = config('services.companies_house.api_key');
        if (empty($apiKey)) {
            return response()->json([
                'message' => 'Companies House API key is not configured. Add COMPANIES_HOUSE_API_KEY to your .env file.',
            ], 422);
        }

        $number = strtoupper(preg_replace('/\s+/', '', (string) $request->input('company_number')));
        if ($number === '') {
            return response()->json(['message' => 'Enter a company number.'], 422);
        }

        $base = 'https://api.company-information.service.gov.uk/company/'.$number;

        $profile = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->get($base);

        if ($profile->status() === 404) {
            return response()->json(['message' => 'No company found for that number.'], 404);
        }

        if (! $profile->successful()) {
            return response()->json([
                'message' => 'Companies House request failed. Check the API key and try again.',
            ], 502);
        }

        $data = $profile->json();

        $officersResponse = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->get($base.'/officers', ['items_per_page' => 100]);

        $directors = [];
        if ($officersResponse->successful()) {
            $items = $officersResponse->json('items');
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    if (! empty($item['resigned_on'])) {
                        continue;
                    }
                    $role = strtolower((string) ($item['officer_role'] ?? ''));
                    if ($role === '' || ! str_contains($role, 'director')) {
                        continue;
                    }
                    $parsed = $this->parseOfficer($item);
                    if ($parsed !== null) {
                        $directors[] = $parsed;
                    }
                }
            }
        }

        $ard = $this->formatAccountingReferenceDate($data['accounts'] ?? []);

        $directorsSummary = collect($directors)
            ->map(fn (array $d) => $d['display_name'])
            ->filter()
            ->take(5)
            ->implode(', ');

        return response()->json([
            'company' => [
                'company_name' => $data['company_name'] ?? '',
                'company_number' => $data['company_number'] ?? $number,
                'company_status' => $data['company_status'] ?? '',
                'incorporation_date' => $data['date_of_creation'] ?? null,
                'accounts_reference_date' => $ard,
                'directors_summary' => $directorsSummary !== '' ? $directorsSummary : null,
            ],
            'directors' => $directors,
        ]);
    }

    /**
     * @param  array<string, mixed>  $accounts
     */
    private function formatAccountingReferenceDate(array $accounts): ?string
    {
        $ard = $accounts['accounting_reference_date'] ?? null;
        if (is_array($ard)) {
            $day = isset($ard['day']) ? (int) $ard['day'] : null;
            $month = isset($ard['month']) ? (int) $ard['month'] : null;
            if ($day !== null && $month !== null && $month >= 1 && $month <= 12) {
                return sprintf('%02d/%02d', $day, $month);
            }
        }

        $periodEnd = $accounts['last_accounts']['period_end_on'] ?? null;
        if (is_string($periodEnd) && $periodEnd !== '') {
            try {
                return Carbon::parse($periodEnd)->format('d/m');
            } catch (\Throwable) {
                // ignore
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function parseOfficer(array $item): ?array
    {
        $elements = $item['name_elements'] ?? null;
        $first = '';
        $last = '';
        $display = '';

        if (is_array($elements)) {
            $fore = trim((string) ($elements['forename'] ?? ''));
            $other = trim((string) ($elements['other_forenames'] ?? ''));
            $sur = trim((string) ($elements['surname'] ?? ''));
            $first = trim($fore.' '.$other);
            $last = $sur;
            $display = trim($sur.($sur !== '' && $first !== '' ? ', ' : '').$first);
        }

        if ($display === '' && isset($item['name'])) {
            $display = trim((string) $item['name']);
            $parts = array_map('trim', explode(',', $display, 2));
            if (count($parts) === 2) {
                $last = $parts[0];
                $first = $parts[1];
            } else {
                $first = $display;
            }
        }

        if ($display === '') {
            return null;
        }

        $dob = $item['date_of_birth'] ?? null;
        $year = null;
        $dobIso = null;
        if (is_array($dob)) {
            $year = isset($dob['year']) ? (int) $dob['year'] : null;
            $month = isset($dob['month']) ? (int) $dob['month'] : null;
            if ($year !== null && $month !== null && $month >= 1 && $month <= 12) {
                $dobIso = sprintf('%04d-%02d-01', $year, $month);
            } elseif ($year !== null) {
                $dobIso = sprintf('%04d-01-01', $year);
            }
        }

        return [
            'display_name' => $display,
            'first_name' => $first,
            'last_name' => $last,
            'year_of_birth' => $year,
            'date_of_birth' => $dobIso,
            'appointed_on' => $item['appointed_on'] ?? null,
        ];
    }
}
