<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    //
    use HasAttributes;
    use HasUuids;
    protected $table = 'weather_data';
    protected $guarded= [];
}
