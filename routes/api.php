<?php

use App\Models\Town;
use App\Models\Weather;
use App\Models\WeatherAggregate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/towns', function (Request $request) {
    return Town::paginate(15);
});

Route::get('/weather/aggregates/{id}', function (Request $request,$id) {
    return Town::find($id)->weatherAggregates()->paginate(15);
});

