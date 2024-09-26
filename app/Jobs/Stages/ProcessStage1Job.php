<?php

namespace App\Jobs\Stages;

use App\Models\Websites;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

class ProcessStage1Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Websites $website;

    public function __construct(Websites $website)
    {
        $this->website = $website;
    }

    /**
     * @throws ConnectionException
     */
    #[NoReturn] public function handle(): void
    {
        $this->website->processing = 1;
        $this->website->save();

        $response = Http::withHeaders([
            'api-key' => config('services.serp.key'),
        ])->get(config('services.serp.url').'/keywords/'.$this->website->serp_id.'/desktop');

        $rankings = collect($response->json()['data']);
        $rankings = $rankings->where('current_position', '<', 5);

        foreach ($this->website->keywords as $keyword) {
            if ($rankings->where('keyword.keyword', $keyword->keyword)->count() > 0) {
                $ranking = $rankings->where('keyword.keyword', $keyword->keyword)->first();
                $keyword->assigned_page = str_replace(rtrim($this->website->website_url, '/'), '', $ranking['ranking_url']);
                $keyword->selection_reason = 'Ranking Position '.$ranking['current_position'];
                $keyword->selected = 0;
                $keyword->new_page = 0;
                $keyword->save();
            }
        }

        $this->website->process_stage = 2;
        $this->website->processing = 0;
        $this->website->save();
    }
}
