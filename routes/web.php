<?php

use App\Models\Weather;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $data = Weather::getWeatherAggregates(102,'hour');
    dd($data);
});
