<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompaniesHouseSearchController extends Controller
{
    /**
     * Search Companies House by name or company number; returns summary rows for the client UI.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        $apiKey = config('services.companies_house.api_key');
        if (empty($apiKey)) {
            return response()->json([
                'message' => 'Companies House API key is not configured. Add COMPANIES_HOUSE_API_KEY to your .env file.',
            ], 422);
        }

        $q = trim((string) $request->input('q'));
        if ($q === '') {
            return response()->json(['message' => 'Enter a company name or number.'], 422);
        }

        $response = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->get('https://api.company-information.service.gov.uk/search/companies', [
                'q' => $q,
                'items_per_page' => 20,
            ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Companies House search failed. Check the API key and try again.',
            ], 502);
        }

        $payload = $response->json();
        $rawItems = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        $items = [];
        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }
            $number = isset($row['company_number']) ? (string) $row['company_number'] : '';
            $title = isset($row['title']) ? (string) $row['title'] : '';
            if ($number === '' && $title === '') {
                continue;
            }
            $items[] = [
                'company_number' => $number,
                'title' => $title !== '' ? $title : $number,
                'subtitle' => $this->formatSearchSubtitle($row),
            ];
        }

        return response()->json(['items' => $items]);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function formatSearchSubtitle(array $item): string
    {
        $number = (string) ($item['company_number'] ?? '');
        $status = strtolower((string) ($item['company_status'] ?? ''));

        if (str_contains($status, 'dissolved')) {
            $date = $item['date_of_cessation'] ?? $item['date_of_creation'] ?? null;
            $label = $date
                ? 'Dissolved on '.Carbon::parse((string) $date)->format('j F Y')
                : 'Dissolved';

            return $number !== '' ? $number.' - '.$label : $label;
        }

        $creation = $item['date_of_creation'] ?? null;
        if ($creation) {
            $line = 'Incorporated on '.Carbon::parse((string) $creation)->format('j F Y');

            return $number !== '' ? $number.' - '.$line : $line;
        }

        return $number !== '' ? $number : '';
    }
}
