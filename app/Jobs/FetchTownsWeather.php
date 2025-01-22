<?php
namespace App\Jobs;

use App\Models\Town;
use App\Models\WeatherRaw;
use App\Services\KafkaServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchTownsWeather implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Town::where('is_active', true)->each(function (Town $town) {
            try {
                $data = $this->fetchWeatherDetails($town->name);
                if (!empty($data['error'])) {
                    Log::error("Location: $town->name || Error: " . json_encode($data['error']));
                    $town->update([
                        'is_active' => false,
                        'remarks' => json_encode($data['error'])
                    ]);
                } else {
                    WeatherRaw::create([
                        'weather_data' => json_encode($data),
                    ]);
                    $kafkaService = new KafkaServices();
                    $kafkaService->produce($data, 'source', 'weather.raw');
                }
            } catch (\Exception $e) {
                Log::error("Exception while processing town: $town->name || Error: " . $e->getMessage());
            }
        });
    }

    /**
     * Fetch weather details for a given city.
     */
    private function fetchWeatherDetails($city)
    {
        if (empty($city)) {
            throw new \Exception('City name is null');
        }
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get('http://api.weatherapi.com/v1/current.json', [
            'key' => env('WEATHERAPI_KEY'),
            'q' => $city,
        ]);
        return $response->json();
    }
}
