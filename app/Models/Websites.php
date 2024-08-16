<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Websites extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'website_url',
        'crawl_location',
        'serp_id',
        'process_stage',
        'processing',
    ];

    public function keywords(): HasMany
    {
        return $this->hasMany(Keywords::class, 'website_id', 'id');
    }

    public function getCrawledPagesData(): Collection
    {
        if (!$this->crawl_location) {
            return collect([]);
        }

        $pagesJson = Storage::disk('s3Crawler')->get($this->crawl_location);

        return collect(json_decode($pagesJson ?? '[]'));
    }

    public function getCurrentStage(): string
    {
        return match ($this->process_stage) {
            0 => "Pending",
            1 => "1/6 - Reviewing Current Ranking Keywords",
            2 => "2/6 - Converting the Website to Vector Database",
            3 => "3/6 - Querying Keywords against Vector Database",
            4 => "4/6 - Select Keywords from Embedding Results",
            5 => "5/6 - Refining Keywords per page",
            6 => "6/6 - Refining Keywords for new pages",
            7 => "Completed",
        };
    }
}
