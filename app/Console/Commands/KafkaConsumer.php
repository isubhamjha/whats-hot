<?php

namespace App\Console\Commands;

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
                topics: [env('CONSUMER_WEATHER_RAW')],
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
            }
        });
        return true;
    }

}
