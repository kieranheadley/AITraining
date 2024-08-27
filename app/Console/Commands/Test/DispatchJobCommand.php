<?php

namespace App\Console\Commands\Test;

use App\Jobs\Stages\ProcessStage5Job;
use App\Models\Websites;
use Illuminate\Console\Command;

class DispatchJobCommand extends Command
{
    protected $signature = 'test:dispatch-job';

    protected $description = 'Command description';

    public function handle(): void
    {
        $website = Websites::find(20559);
        ProcessStage5Job::dispatchSync($website);
    }
}
