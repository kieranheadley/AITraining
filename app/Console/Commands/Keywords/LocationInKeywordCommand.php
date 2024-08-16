<?php

namespace App\Console\Commands\Keywords;

use App\Models\KeywordIndex;
use App\Models\KeywordLocations;
use Illuminate\Console\Command;

class LocationInKeywordCommand extends Command
{
    protected $signature = 'keywords:location-in-keyword';

    protected $description = 'Review the keyword to check if a location exists in the keyword';

    public function handle(): void
    {
        ini_set('memory_limit', '256M');

        $locations = KeywordLocations::select('location_name')->where('location_type', '!=', 'Postal Code')->whereIn('location_country_iso_code', ['GB', 'US', 'AU'])->pluck('location_name')->unique()->toArray();

        $keywords = KeywordIndex::whereNull('location_in_keyword')->whereIn('country', ['GB', 'US', 'AU'])->limit(1000)->cursor();

        foreach ($keywords as $keyword) {
            $found = 0;

            foreach ($locations as $location) {
                if (preg_match('/\b' . preg_quote($location, '/') . '\b/i', $keyword->keyword)) {
                    $keyword->location_in_keyword = 1;
                    $keyword->save();
                    $found = 1;
                    break;
                }
            }

            if ($found == 0) {
                $keyword->location_in_keyword = 0;
                $keyword->save();
            }
        }
    }
}
