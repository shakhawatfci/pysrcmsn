<?php

namespace App\Traits;

trait OnlineCurrency
{

    public function getOnlineCurrencyRateList($url): array
    {
        
        $response = file_get_contents($url);
        if (!$response) {
            return [];
        }
        $rates = json_decode($response, true);
        return $rates['rates'] ?? [];
    }

}
