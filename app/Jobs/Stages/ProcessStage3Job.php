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

        $crawl = $this->website->getCrawledPagesData()->unique('url');

        foreach ($crawl->chunk(20) as $chunk) {
            $vectors = [];

            foreach ($chunk as $page) {
                $meta = $page->path.' - '.$page->title.' - '.$page->meta_desc;
                $headings = implode(', ', $page->h1_headings).' - '.implode(', ', $page->h2_headings);

                $vectors[] = [
                    'id' => str_replace('/', '|', $page->path).'#meta',
                    'values' => $openai->generateEmbeddings($meta),
                ];
                $vectors[] = [
                    'id' => str_replace('/', '|', $page->path).'#headings',
                    'values' => $openai->generateEmbeddings($headings),
                ];
            }

            $pinecone->upsertVectors($vectors, $this->website);
        }

        $this->website->process_stage = 4;
        $this->website->processing = 0;
        $this->website->save();
    }
}
