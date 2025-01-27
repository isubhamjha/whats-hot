<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Town extends Model
{
    //
    protected $guarded = [];

    public function weatherAggregates(): HasMany
    {
        return $this->hasMany(WeatherAggregate::class);
    }
}
