<?php

namespace App\Jobs\SERP;

use App\Models\Keywords;
use App\Services\DataForSEOService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetRankingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Keywords $keyword;

    public function __construct(Keywords $keyword)
    {
        $this->keyword = $keyword;
    }

    public function handle(DataForSEOService $dataForSEO): void
    {
        $rankingSites = [];

        $rankings = $dataForSEO->getRankings($this->keyword->keyword);

        if ($rankings) {
            if($rankings['tasks'][0]['result'][0]['items']) {
                foreach ($rankings['tasks'][0]['result'][0]['items'] as $item) {
                    if(count($rankingSites) < 10) {
                        $rankingSites[] = [
                            'url' => $item['url'],
                            'rank' => $item['rank_group'],
                            'title' => $item['title'],
                            'description' => $item['description']
                        ];
                    }
                }
            }
        }

        $this->keyword->ranking_sites = $rankingSites;
        $this->keyword->save();
    }
}
