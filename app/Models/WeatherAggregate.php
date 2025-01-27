<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherAggregate extends Model
{
    //
    protected $guarded = [];
    protected $primaryKey = 'town_id,duration';

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class,'town_id','town_id');
    }
}
