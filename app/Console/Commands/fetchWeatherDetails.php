<?php

namespace App\Console\Commands;

use App\Models\WeatherRaw;
use App\Services\KafkaServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class fetchWeatherDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-weather-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get(
            'http://api.weatherapi.com/v1/current.json',
            [
                'key' => 'ef085edc9ca9477d908163140251001',
                'q' => 'katihar'
            ]
        );
        $data = $response->json();
//        if (!empty($data)) {
//            WeatherRaw::create([
//               'weather_data' => json_encode($data)
//            ]);
//        }
        $kafkaService = new KafkaServices();
        $kafkaService->produce($data,'source','weather.raw');

    }
}
