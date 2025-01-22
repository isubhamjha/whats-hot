<?php

namespace App\Console\Commands;

use App\Models\Town;
use App\Models\WeatherRaw;
use App\Services\KafkaServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use const Widmogrod\Functional\emptyM;

class fetchWeatherDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        Town::where('is_active',true)->each(function (Town $town) {
            $data = $this->fetchWeatherDetails($town->name);
            if (!empty($data['error'])){
                Log::error("Location: $town->name|| Error:".json_encode($data['error']));
                $town->update([
                    'is_active' => false,
                    'remarks' => json_encode($data['error'])
                ]);
            }else {
                $kafkaService = new KafkaServices();
                $kafkaService->produce($data, 'source', 'weather.raw');
            }
        });
    }
    public function fetchWeatherDetails($city)
    {
        if (empty($city))
            throw new \Exception('city is null');
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get(
            'http://api.weatherapi.com/v1/current.json',
            [
                'key' => env('WEATHERAPI_KEY'),
                'q' => $city
            ]
        );
        return $response->json();
    }
}
