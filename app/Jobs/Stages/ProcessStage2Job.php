<?php

namespace App\Jobs\Stages;

use App\Jobs\SERP\GetRankingsJob;
use App\Models\KeywordIndex;
use App\Models\Websites;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStage2Job implements ShouldQueue
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

        $keywords = $this->website->keywords;

        foreach ($keywords as $keyword) {
            GetRankingsJob::dispatch($keyword);
        }

        $locations = $openai->getLocationInKeyword($keywords->pluck('keyword')->toArray());
        $locations = current(json_decode($locations));

        KeywordIndex::whereIn('keyword', $locations)
            ->update(['location_in_keyword' => 1]);

        $intents = $openai->getSearchIntent($keywords->pluck('keyword')->toArray());
        $intents = current(json_decode($intents));

        foreach ($intents as $intent) {
            KeywordIndex::where('keyword', $intent->keyword)
                ->update(['search_intent' => $intent->intent]);
        }

        $this->website->process_stage = 3;
        $this->website->processing = 0;
        $this->website->save();
    }
}
