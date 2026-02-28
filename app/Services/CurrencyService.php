<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Currencies supported in the UI.
     * Format: code => [name, symbol]
     */
    const SUPPORTED = [
        'IDR' => ['Indonesian Rupiah', 'Rp'],
        'USD' => ['US Dollar', '$'],
        'EUR' => ['Euro', '€'],
        'SGD' => ['Singapore Dollar', 'S$'],
        'MYR' => ['Malaysian Ringgit', 'RM'],
        'JPY' => ['Japanese Yen', '¥'],
        'GBP' => ['British Pound', '£'],
        'AUD' => ['Australian Dollar', 'A$'],
        'CNY' => ['Chinese Yuan', '¥'],
        'SAR' => ['Saudi Riyal', '﷼'],
    ];

    /**
     * Fetch exchange rates from open.er-api.com (free, no key required).
     * Base is IDR, so rates["USD"] means "1 IDR = X USD".
     * Cached for 24 hours.
     *
     * Returns array: currency_code => rate_from_IDR  (e.g. ["USD" => 0.0000625])
     */
    public function getRates(): array
    {
        return Cache::remember('currency_rates', now()->addHours(24), function () {
            try {
                $response = Http::timeout(5)
                    ->get('https://open.er-api.com/v6/latest/IDR');

                if ($response->successful()) {
                    return $response->json('rates', []);
                }
            } catch (\Throwable $e) {
                Log::warning('CurrencyService: failed to fetch rates — ' . $e->getMessage());
            }

            // Fallback hardcoded rates (approximate, updated Feb 2026)
            return [
                'IDR' => 1,
                'USD' => 0.0000625,   // ~16,000 IDR
                'EUR' => 0.0000578,   // ~17,300 IDR
                'SGD' => 0.0000840,   // ~11,900 IDR
                'MYR' => 0.000297,    // ~3,370 IDR
                'JPY' => 0.00956,     // ~104.6 IDR
                'GBP' => 0.0000490,   // ~20,400 IDR
                'AUD' => 0.0000980,   // ~10,200 IDR
                'CNY' => 0.000453,    // ~2,208 IDR
                'SAR' => 0.000234,    // ~4,274 IDR
            ];
        });
    }

    /**
     * Returns how many IDR equals 1 unit of the given currency.
     * e.g. idrPerUnit('USD') → 16000
     */
    public function idrPerUnit(string $currency): float
    {
        if (strtoupper($currency) === 'IDR') {
            return 1.0;
        }

        $rates = $this->getRates();
        $rateFromIDR = $rates[strtoupper($currency)] ?? null;

        if (!$rateFromIDR || $rateFromIDR == 0) {
            return 1.0;
        }

        return round(1 / $rateFromIDR, 4);
    }

    /**
     * Convert an amount from a source currency to IDR.
     */
    public function toIDR(int|float $amount, string $currency, ?float $storedRate = null): float
    {
        if (strtoupper($currency) === 'IDR') {
            return $amount;
        }

        $rate = $storedRate ?? $this->idrPerUnit($currency);
        return $amount * $rate;
    }

    public static function symbol(string $currency): string
    {
        return self::SUPPORTED[strtoupper($currency)][1] ?? strtoupper($currency);
    }

    public static function name(string $currency): string
    {
        return self::SUPPORTED[strtoupper($currency)][0] ?? strtoupper($currency);
    }
}
