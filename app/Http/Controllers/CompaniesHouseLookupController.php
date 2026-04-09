<?php

namespace App\Http\Controllers;

use App\Models\ClientType;
use App\Models\CompanyStatus;
use App\Models\SicCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CompaniesHouseLookupController extends Controller
{
    /**
     * Return company profile fields for the client form (JSON).
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

        $url = 'https://api.company-information.service.gov.uk/company/'.$number;

        $response = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->get($url);

        if ($response->status() === 404) {
            return response()->json(['message' => 'No company found for that number.'], 404);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Companies House request failed. Check the API key and try again.',
            ], 502);
        }

        $data = $response->json();

        $registered = $data['registered_office_address'] ?? [];
        $addressLines = array_filter([
            $registered['premises'] ?? null,
            $registered['address_line_1'] ?? null,
            $registered['address_line_2'] ?? null,
            $registered['locality'] ?? null,
            $registered['region'] ?? null,
            $registered['postal_code'] ?? null,
            $registered['country'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $registeredAddress = implode("\n", $addressLines);

        $statusName = $this->mapCompanyStatus($data['company_status'] ?? null);
        $statusId = null;
        if ($statusName !== null) {
            $statusId = CompanyStatus::query()->where('name', $statusName)->value('id');
        }

        $sicCodeId = null;
        $sicCodes = $data['sic_codes'] ?? [];
        if (is_array($sicCodes) && $sicCodes !== []) {
            $first = (string) ($sicCodes[0] ?? '');
            $sicCodeId = SicCode::query()->where('code', $first)->value('id');
        }

        $incorporation = $data['date_of_creation'] ?? null;

        $suggestedClientTypeId = $this->suggestClientTypeId($data);

        return response()->json([
            'suggested_name' => $data['company_name'] ?? null,
            'suggested_client_type_id' => $suggestedClientTypeId,
            'company' => [
                'company_number' => $data['company_number'] ?? $number,
                'company_status_id' => $statusId ? (string) $statusId : '',
                'incorporation_date' => $incorporation ?: '',
                'registered_address' => $registeredAddress,
                'sic_code_id' => $sicCodeId ? (string) $sicCodeId : '',
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function suggestClientTypeId(array $profile): ?string
    {
        $t = isset($profile['type']) ? strtolower((string) $profile['type']) : '';
        $name = match ($t) {
            'ltd' => 'Private Limited Company',
            'llp' => 'Limited Liability Partnership (LLP)',
            default => null,
        };
        if ($name === null) {
            return null;
        }

        $id = ClientType::query()->where('name', $name)->where('is_active', true)->value('id');

        return $id !== null ? (string) $id : null;
    }

    private function mapCompanyStatus(?string $apiStatus): ?string
    {
        if ($apiStatus === null || $apiStatus === '') {
            return null;
        }

        $key = Str::lower(str_replace('-', ' ', $apiStatus));

        return match ($key) {
            'active' => 'Active',
            'dormant' => 'Dormant',
            'dissolved' => 'Dissolved',
            'liquidation', 'in liquidation' => 'In Liquidation',
            'administration', 'in administration' => 'In Administration',
            default => Str::title($apiStatus),
        };
    }
}
