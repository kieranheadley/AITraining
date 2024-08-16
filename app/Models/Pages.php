<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pages extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'website_id',
        'page_url',
        'page_path',
        'page_title',
        'meta_description',
        'headings',
    ];

    protected function casts()
    {
        return [
            'headings' => 'array',
        ];
    }
}
