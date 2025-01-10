<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WeatherRaw extends Model
{
    use HasUuids;
    protected $table = 'weather_raw';
    protected $guarded = [];

}
