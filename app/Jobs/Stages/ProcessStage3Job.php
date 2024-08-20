<?php

namespace App\Jobs\Stages;

use App\Models\Websites;
use App\Services\OpenAIService;
use App\Services\PineconeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStage3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Websites $website;

    public function __construct(Websites $website)
    {
        $this->onQueue('long');
        $this->website = $website;
    }

    public function handle(OpenAIService $openai, PineconeService $pinecone): void
    {
        $this->website->processing = 1;
        $this->website->save();

        $keywords = $this->website->keywords->whereNull('assigned_page');

        foreach ($keywords as $keyword) {
            $searchEmbedding = $openai->generateEmbeddings($keyword->keyword);
            $results = $pinecone->queryVectors($searchEmbedding, $this->website);

            $embeddingResults = $urls = [];

            if (!isset($results['error'])) {
                foreach ($results['matches'] as $match) {
                    $match['id'] = str_replace('|', '/', $match['id']);
                    $url = explode('#', $match['id'])[0];

                    if (!in_array($url, $urls) && count($urls) < 3) {
                        $urls[] = $url;
                        $embeddingResults[] = ['url' => $url, 'score' => $match['score']];
                    }
                }
            }

            $keyword->embedding_results = $embeddingResults;
            $keyword->save();
        }

        $this->website->process_stage = 4;
        $this->website->processing = 0;
        $this->website->save();
    }
}
