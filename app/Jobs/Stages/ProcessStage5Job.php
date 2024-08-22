<?php

namespace App\Jobs\Stages;

use App\Models\Websites;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStage5Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Websites $website;

    public function __construct(Websites $website)
    {
        $this->onQueue('long');
        $this->website = $website;
    }

    public function handle(OpenAIService $openai): void
    {
        $this->website->processing = 1;
        $this->website->save();

        $keywords = $this->website->keywords->whereNull('assigned_page')->whereNotNull('embedding_results');

        $crawl = collect($this->website->getCrawledPagesData()->unique('url'));

        foreach ($keywords as $keyword) {
            $pages = [];
            foreach ($keyword->embedding_results as $page) {
                $pages[] = $crawl->where('path', $page['url'])->first();
            }

            $response = $openai->selectPageFromEmbeddings($keyword, $pages);

            if ($response !== 'error') {
                $response = explode(' - ', $response);

                if ($response[0] === 'new page') {
                    $keyword->assigned_page = null;
                    $keyword->new_page = 1;
                }else{
                    $keyword->assigned_page = parse_url($response[0])['path'];
                    $keyword->new_page = 0;
                }

                $keyword->selection_reason = $response[1];
                $keyword->save();
            }
        }

        $this->website->process_stage = 6;
        $this->website->processing = 0;
        $this->website->save();
    }
}
