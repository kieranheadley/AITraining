<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keywords extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'website_id',
        'keyword',
        'hike_assigned_page',
        'assigned_page',
        'embedding_results',
        'embedding_reason',
        'selected',
        'new_page',
        'assignment_flagged',
        'assignment_flag_reason',
        'assignment_flag_notes',
    ];

    protected function casts()
    {
        return [
            'embedding_results' => 'array',
        ];
    }

    public function website(): HasOne
    {
        return $this->hasOne(Websites::class, 'id', 'website_id');
    }
}
