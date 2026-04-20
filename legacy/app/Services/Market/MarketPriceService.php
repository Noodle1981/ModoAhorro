<?php

namespace App\Services\Market;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketPriceService
{
    /**
     * Fetch the average market price for a given search term from Mercado Libre (MLA - Argentina).
     *
     * @param string $searchTerm
     * @return float|null
     */
    public function fetchPrice(string $searchTerm): ?float
    {
        try {
            $token = $this->getAccessToken();
            
            // Search in Mercado Libre Argentina (MLA)
            $response = Http::withToken($token)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
                ->get("https://api.mercadolibre.com/sites/MLA/search", [
                    'q' => $searchTerm,
                    'limit' => 10,
                    'condition' => 'new',
                    'status' => 'active'
                ]);

            if ($response->failed()) {
                Log::error("Mercado Libre API Error for term '{$searchTerm}': " . $response->body());
                return null;
            }

            $results = $response->json('results', []);

            if (empty($results)) {
                Log::warning("No results found in Mercado Libre for: {$searchTerm}");
                return null;
            }

            $prices = collect($results)->pluck('price');
            
            // Filter outliers
            if ($prices->count() >= 5) {
                $prices = $prices->sort()->slice(1, -1);
            }

            return $prices->avg();

        } catch (\Exception $e) {
            Log::error("Exception fetching Mercado Libre price: " . $e->getMessage());
            return null;
        }
    }

    private function getAccessToken(): string
    {
        // Try to get from cache first
        if ($token = \Illuminate\Support\Facades\Cache::get('meli_access_token')) {
            return $token;
        }

        // Request new token using Client Credentials Flow (if supported by app type)
        // Note: Public ML apps might not support Client Credentials for search, 
        // but often just having a valid token (even basic) or App ID helps.
        // If this fails, we might need to rely on the App ID in headers.
        
        try {
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.mercadolibre.app_id'),
                'client_secret' => config('services.mercadolibre.client_secret'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 21600;

                \Illuminate\Support\Facades\Cache::put('meli_access_token', $token, $expiresIn - 60);
                return $token;
            }
            
            Log::error('Failed to get Meli Token: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception getting Meli Token: ' . $e->getMessage());
        }

        return ''; // Return empty string to try request without token (or fail downstream)
    }
}
