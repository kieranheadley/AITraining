<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordIndex extends Model
{
    protected $table = 'keyword_index';

    protected $fillable = [
        'keyword',
        'language',
        'country',
        'search_volume',
        'difficulty',
        'calculated_difficulty',
        'search_intent',
        'location_in_keyword',
        'location_reviewed',
    ];
}
