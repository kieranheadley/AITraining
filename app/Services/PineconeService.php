<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PineconeService
{
    public function upsertVectors($vectors, $website): void
    {
         Http::withHeaders([
            'Api-Key' => config('services.pinecone.api_key'),
            'Content-Type' => 'application/json',
            'X-Pinecone-API-Version' => '2024-07',
        ])->post(config('services.pinecone.index_host').'/vectors/upsert', [
            'vectors' => $vectors,
            'namespace' => 'ns'.$website->id,
        ]);
    }

    public function queryVectors($vector, $website): array
    {
        $response = Http::withHeaders([
            'Api-Key' => config('services.pinecone.api_key'),
            'Content-Type' => 'application/json',
            'X-Pinecone-API-Version' => '2024-07',
        ])->post(config('services.pinecone.index_host').'/query', [
            'namespace' => 'ns'.$website->id,
            'vector' => $vector,
            'topK' => 6,
            'includeValues' => false,
        ]);

        return $response->json();
    }
}
