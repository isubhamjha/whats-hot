<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use function PHPUnit\Framework\isJson;

class KafkaServices
{
    public string $topic;
    public string $consumerGroup;
    public string $kafkaBroker;
    protected string $dlqTopic;
    private bool $isDlqEnabled = true;
    private bool $isBatchConsumptionEnabled = true;
    private int $batchSizeLimit = 1;
    private int $batchReleaseInterval = 10000;

    public function __construct()
    {
        $this->topic = env('KAFKA_CONSUMER_TOPIC');
        $this->consumerGroup = env('KAFKA_CONSUMER_GROUP_ID');
        $this->kafkaBroker = env('KAFKA_BROKERS');
        $this->dlqTopic = env('KAFKA_DLQ_TOPIC', env('KAFKA_PRODUCER_TOPIC') . '.DLQ');
    }

    public function produce(string|array $payload, string $origin_service = null, string $topic = null, string $key = null): void
    {
        try {
            $topic ??= env('KAFKA_PRODUCER_TOPIC');
            $origin_service ??= env('KAFKA_SERVICE');
            $message = new Message(
                headers: ['content_type' => 'application/json', 'origin_service' => $origin_service],
                body: Str::isJson($payload) ? json_decode($payload, true) : $payload,
                key: $key
            );

            Kafka::asyncPublish($this->kafkaBroker)
                ->onTopic($topic)
                ->withMessage($message)
                ->send();

            Log::info("Message produced to topic {$topic}", ['payload' => $payload]);
        } catch (\Exception $e) {
            Log::error("Failed to produce message to topic {$topic}: " . $e->getMessage());
        }
    }

    public function consume(string $topic = null, string $groupId = null, callable $handler = null): self
    {
        try {
            $this->topic = $topic ?? $this->topic;
            $this->consumerGroup = $groupId ?? $this->consumerGroup;

            $consumer = Kafka::consumer([$this->topic], $this->consumerGroup, $this->kafkaBroker)
                ->withHandler(function (ConsumerMessage|Collection $message) use ($handler) {
                    $handler ? $handler($message) : $this->handleMessage($message);
                })
                ->withAutoCommit()
                ->onStopConsuming(fn() => Log::warning("Consumer stopped for topic: {$this->topic}"));

            if ($this->isDlqEnabled && !empty($this->dlqTopic)) {
                $consumer->withDlq($this->dlqTopic);
            }

            $consumer->enableBatching()
                ->withBatchSizeLimit($this->batchSizeLimit)
                ->withBatchReleaseInterval($this->batchReleaseInterval)
                ->build()
                ->consume();
        } catch (\Exception $e) {
            Log::error('Failed to consume messages: ' . $e->getMessage());
        }
        return $this;
    }

    protected function handleMessage(Collection $messages): void
    {
        $messages->each(function ($message) {
            if ($message->getTopicName() === env('KAFKA_CONSUMER_TOPIC')) {
                Log::info('Message handled');
            }
        });
    }

    public function disableDLQ($dlq_topic = null): self
    {
        $this->isDlqEnabled = false;
//        $this->dlqTopic = $dlq_topic ?? $this->dlqTopic;
        return $this;
    }
}
