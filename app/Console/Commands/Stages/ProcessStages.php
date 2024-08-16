<?php

namespace App\Console\Commands\Stages;

use App\Jobs\Stages\ProcessStage1Job;
use App\Jobs\Stages\ProcessStage2Job;
use App\Jobs\Stages\ProcessStage3Job;
use App\Jobs\Stages\ProcessStage4Job;
use App\Jobs\Stages\ProcessStage5Job;
use App\Jobs\Stages\ProcessStage6Job;
use App\Models\Websites;
use Illuminate\Console\Command;

class ProcessStages extends Command
{
    protected $signature = 'stages:process';

    protected $description = 'Dispatches jobs to process stages';

    public function handle(): void
    {
        $subQuery = Websites::selectRaw('MIN(id) as id')
            ->where('process_stage', '>', 0)
            ->where('process_stage', '<>', 7)
            ->where('processing', '=', 0)
            ->groupBy('process_stage');

        $websites = Websites::whereIn('id', $subQuery)
            ->get();

        foreach ($websites as $website) {
            if ($website->process_stage == 1) {
                ProcessStage1Job::dispatch($website);
            } elseif ($website->process_stage == 2) {
                ProcessStage2Job::dispatch($website);
            } elseif ($website->process_stage == 3) {
                ProcessStage3Job::dispatch($website);
            } elseif ($website->process_stage == 4) {
                ProcessStage4Job::dispatch($website);
            } elseif ($website->process_stage == 5) {
                ProcessStage5Job::dispatch($website);
            } elseif ($website->process_stage == 6) {
                ProcessStage6Job::dispatch($website);
            }
        }
    }
}
