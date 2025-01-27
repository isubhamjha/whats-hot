<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use InvalidArgumentException;

class Weather extends Model
{
    //
    use HasAttributes;
    use HasUuids;
    protected $table = 'weather_data';
    protected $guarded= [];

    public static function getWeatherAggregates($town_id,$timePeriod = 'hour'): Collection
    {
        // Define time range based on the time period
        $startTime = match ($timePeriod) {
            'hour' => Carbon::now()->subHour(),
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            default => throw new InvalidArgumentException('Invalid time period specified'),
        };

        // Query the database for aggregate values
        return DB::table('weather_data')
            ->select([
                DB::raw('AVG(temp_c) as avg_temp_c'),
                DB::raw('AVG(temp_f) as avg_temp_f'),
                DB::raw('AVG(humidity) as avg_humidity'),
                DB::raw('AVG(pressure_mb) as avg_pressure_mb'),
                DB::raw('AVG(precip_mm) as avg_precip_mm'),
                DB::raw('MAX(temp_c) as max_temp_c'),
                DB::raw('MIN(temp_c) as min_temp_c'),
                DB::raw('MAX(humidity) as max_humidity'),
                DB::raw('MIN(humidity) as min_humidity'),
                DB::raw('COUNT(*) as total_records'),
            ])
            ->where('recorded_at', '>=', $startTime)
            ->where('town_id', '=', $town_id)
            ->get();
    }

}



