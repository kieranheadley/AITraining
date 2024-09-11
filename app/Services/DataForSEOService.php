<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DataForSEOService
{
    public function getRankings($keyword)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.dataforseo.username').':'.config('services.dataforseo.password')),
            'Content-Type' => 'application/json',
        ])->post('https://api.dataforseo.com/v3/serp/google/organic/live/regular', [
            [
                'language_code' => 'en',
                'location_code' => 2826,
                'keyword' => $keyword,
            ]
        ]);

        // Handle the response
        if ($response->successful()) {
            // Process the successful response
            return $response->json();
        } else {
            // Handle the error
            dump($response->status(), $response->body());
            return null;
        }
    }
}
