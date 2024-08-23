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
        'primary_location',
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
            1 => "1/7 - Reviewing Current Ranking Keywords",
            2 => "2/7 - Analyzing Keywords for Intent & Location",
            3 => "3/7 - Converting the Website to Vector Database",
            4 => "4/7 - Querying Keywords against Vector Database",
            5 => "5/7 - Select Keywords from Embedding Results",
            6 => "6/7 - Refining Keywords per page",
            7 => "7/7 - Refining Keywords for new pages",
            8 => "Completed"
        };
    }
}
