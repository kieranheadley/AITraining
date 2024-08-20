<?php

namespace App\Jobs\Stages;

use App\Models\Keywords;
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
        $this->website = $website;
    }

    public function handle(OpenAIService $openai): void
    {
        $this->website->processing = 1;
        $this->website->save();

        $crawl = collect($this->website->getCrawledPagesData()->unique('url'));

        $pageKeywords = Keywords::select('assigned_page', 'keyword')
            ->where('website_id', $this->website->id)
            ->where('new_page', '=', 0)
            ->get()
            ->groupBy('assigned_page')
            ->map(function ($group) {
                return $group->pluck('keyword')->toArray();
            })
            ->toArray();

        foreach ($pageKeywords as $page => $keywords) {
            if (count($keywords) <= 3) {
                Keywords::where('website_id', $this->website->id)
                    ->whereIn('keyword', $keywords)
                    ->update(['selected' => 1]);

                unset($pageKeywords[$page]);
            } else {
                $pageCrawl = $crawl->where('url', $page)->first();

                if(!$pageCrawl) {
                    continue;
                }

                $gpt = $openai->selectKeywordForExistingPage($keywords, $pageCrawl);

                $gptKeywords = current(json_decode($gpt, true));

                $keywords = array_diff($keywords, $gptKeywords);

                Keywords::where('website_id', $this->website->id)
                    ->whereIn('keyword', $gptKeywords)
                    ->update(['selected' => 1]);

                Keywords::where('website_id', $this->website->id)
                    ->whereIn('keyword', $keywords)
                    ->update(['selected' => 0]);
            }
        }

        $this->website->process_stage = 6;
        $this->website->processing = 0;
        $this->website->save();
    }
}
