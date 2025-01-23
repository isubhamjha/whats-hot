<?php

namespace App\Console\Commands;

use App\Models\Weather;
use App\Models\WeatherRaw;
use App\Services\KafkaServices;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class KafkaConsumer extends Command
{
    protected $signature = 'kafka:consumer';
    protected $description = 'Command for Kafka consumer';
    protected KafkaServices $kafkaService;

    public function __construct(KafkaServices $kafkaService)
    {
        parent::__construct();
        $this->kafkaService = $kafkaService;
    }

    public function handle(): void
    {
        $this->kafkaService
            ->consume(
                topics: [
                    env('CONSUMER_WEATHER_RAW'),
                    env('CONSUMER_WEATHER_DETAILS'),
                    env('CONSUMER_WEATHER_DETAILS_PROCESSED')
                ],
                groupId: null,
                handler: function (Collection $message) {
                    $this->processTopics($message);
                }
            );
    }

    protected function processTopics(Collection $messages): true
    {
        $messages->each(function ($message) {
            if ($message->getTopicName() === env('CONSUMER_WEATHER_RAW')) {
                $payload = $message->getBody();
                WeatherRaw::create([
                    'weather_data' => json_encode($payload),
                ]);
                $this->kafkaService->produce(
                    json_encode($payload),
                    $message->getTopicName(),
                    env('CONSUMER_WEATHER_DETAILS')
                );
            }
            if ($message->getTopicName() === env('CONSUMER_WEATHER_DETAILS')) {
                $data = $message->getBody();
                $processed_weather_data = [
                    'town_id' => $data['town_id'],
                    'city_name' => $data['data']['location']['name'],
                    'region' => $data['data']['location']['region'],
                    'country' => $data['data']['location']['country'],
                    'lat' => $data['data']['location']['lat'],
                    'lon' => $data['data']['location']['lon'],
                    'tz_id' => $data['data']['location']['tz_id'],
                    'localtime' => $data['data']['location']['localtime'],
                    'temp_c' => $data['data']['current']['temp_c'],
                    'temp_f' => $data['data']['current']['temp_f'],
                    'is_day' => $data['data']['current']['is_day'],
                    'condition' => json_encode($data['data']['current']['condition']),
                    'wind_mph' => $data['data']['current']['wind_mph'],
                    'wind_kph' => $data['data']['current']['wind_kph'],
                    'wind_degree' => $data['data']['current']['wind_degree'],
                    'wind_dir' => $data['data']['current']['wind_dir'],
                    'pressure_mb' => $data['data']['current']['pressure_mb'],
                    'pressure_in' => $data['data']['current']['pressure_in'],
                    'precip_mm' => $data['data']['current']['precip_mm'],
                    'precip_in' => $data['data']['current']['precip_in'],
                    'humidity' => $data['data']['current']['humidity'],
                    'cloud' => $data['data']['current']['cloud'],
                    'feelslike_c' => $data['data']['current']['feelslike_c'],
                    'feelslike_f' => $data['data']['current']['feelslike_f'],
                    'windchill_c' => $data['data']['current']['windchill_c'],
                    'windchill_f' => $data['data']['current']['windchill_f'],
                    'heatindex_c' => $data['data']['current']['heatindex_c'],
                    'heatindex_f' => $data['data']['current']['heatindex_f'],
                    'dewpoint_c' => $data['data']['current']['dewpoint_c'],
                    'dewpoint_f' => $data['data']['current']['dewpoint_f'],
                    'vis_km' => $data['data']['current']['vis_km'],
                    'vis_miles' => $data['data']['current']['vis_miles'],
                    'uv' => $data['data']['current']['uv'],
                    'gust_mph' => $data['data']['current']['gust_mph'],
                    'gust_kph' => $data['data']['current']['gust_kph'],
                    'recorded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                Weather::create($processed_weather_data);
                $this->kafkaService->produce(
                    json_encode($processed_weather_data),
                    $message->getTopicName(),
                    env('CONSUMER_WEATHER_DETAILS_PROCESSED')
                );
            }
            if ($message->getTopicName() === env('CONSUMER_WEATHER_DETAILS_PROCESSED')) {

            }
        });
        return true;
    }

}
