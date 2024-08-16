<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordLocations extends Model
{
    protected $fillable = [
        'location_id',
        'location_id_parent',
        'location_name',
        'location_type',
        'location_country_iso_code',
    ];
}
