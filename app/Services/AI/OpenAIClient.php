<?php

namespace App\Services\AI;

use App\Models\AiUsageLog;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIClient
{
    private string $apiKey;

    private string $model;

    private int $maxTokens;

    private float $temperature;

    private int $timeout;

    private int $retries;

    private int $retryDelayMs;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->model = config('ai.openai.model');
        $this->maxTokens = config('ai.openai.max_tokens');
        $this->temperature = config('ai.openai.temperature');
        $this->timeout = config('ai.openai.timeout');
        $this->retries = config('ai.openai.retries');
        $this->retryDelayMs = config('ai.openai.retry_delay_ms');
    }

    /**
     * Send a chat completion request to OpenAI.
     *
     * @param  array  $messages  Array of message objects with role and content.
     * @param  array|null  $responseFormat  Optional JSON schema for structured outputs.
     * @param  array  $options  Additional options to merge into the request payload.
     * @return array The parsed response content.
     *
     * @throws \RuntimeException
     */
    public function chat(array $messages, ?array $responseFormat = null, array $options = []): array
    {
        $startTime = microtime(true);
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retries) {
            $attempt++;

            try {
                $payload = array_merge([
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ], $options);

                if ($responseFormat !== null) {
                    $payload['response_format'] = [
                        'type' => 'json_schema',
                        'json_schema' => $responseFormat,
                    ];
                }

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://api.openai.com/v1/chat/completions', $payload);

                if ($response->status() === 429) {
                    $this->logUsage(
                        action: 'rate_limited',
                        inputTokens: 0,
                        outputTokens: 0,
                        totalTokens: 0,
                        durationMs: $this->elapsedMs($startTime),
                        status: 'rate_limited',
                        errorMessage: 'Rate limit exceeded',
                    );

                    $retryAfter = $response->header('Retry-After', (int) ceil($this->retryDelayMs * pow(2, $attempt - 1) / 1000));
                    usleep((int) $retryAfter * 1_000_000);
                    $lastException = new \RuntimeException('Rate limit exceeded (429)');

                    continue;
                }

                $response->throw();

                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $usage = $data['usage'] ?? [];

                $inputTokens = $usage['prompt_tokens'] ?? 0;
                $outputTokens = $usage['completion_tokens'] ?? 0;
                $totalTokens = $usage['total_tokens'] ?? 0;

                $this->logUsage(
                    action: $options['_action'] ?? 'chat',
                    inputTokens: $inputTokens,
                    outputTokens: $outputTokens,
                    totalTokens: $totalTokens,
                    durationMs: $this->elapsedMs($startTime),
                    status: 'success',
                    loggableType: $options['_loggable_type'] ?? null,
                    loggableId: $options['_loggable_id'] ?? null,
                );

                $parsed = json_decode($content, true);

                return is_array($parsed) ? $parsed : ['content' => $content];
            } catch (RequestException $e) {
                $lastException = $e;

                Log::warning('OpenAI API request failed', [
                    'attempt' => $attempt,
                    'status' => $e->response?->status(),
                    'message' => $e->getMessage(),
                ]);

                if ($attempt < $this->retries) {
                    usleep($this->retryDelayMs * pow(2, $attempt - 1) * 1000);
                }
            }
        }

        $this->logUsage(
            action: $options['_action'] ?? 'chat',
            inputTokens: 0,
            outputTokens: 0,
            totalTokens: 0,
            durationMs: $this->elapsedMs($startTime),
            status: 'failed',
            errorMessage: $lastException?->getMessage() ?? 'Unknown error after retries',
            loggableType: $options['_loggable_type'] ?? null,
            loggableId: $options['_loggable_id'] ?? null,
        );

        throw new \RuntimeException(
            'OpenAI API request failed after '.$this->retries.' attempts: '.($lastException?->getMessage() ?? 'Unknown error')
        );
    }

    /**
     * Estimate the cost of a request based on token counts.
     * GPT-4o pricing: $2.50 per 1M input tokens, $10.00 per 1M output tokens.
     */
    public function estimateCost(int $inputTokens, int $outputTokens): float
    {
        $inputCost = ($inputTokens / 1_000_000) * 2.50;
        $outputCost = ($outputTokens / 1_000_000) * 10.00;

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Log usage to the ai_usage_logs table.
     */
    private function logUsage(
        string $action,
        int $inputTokens,
        int $outputTokens,
        int $totalTokens,
        int $durationMs,
        string $status,
        ?string $errorMessage = null,
        ?string $loggableType = null,
        ?int $loggableId = null,
    ): void {
        try {
            AiUsageLog::create([
                'company_id' => auth()->user()?->company_id,
                'user_id' => auth()->id(),
                'loggable_type' => $loggableType,
                'loggable_id' => $loggableId,
                'action' => $action,
                'model' => $this->model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $totalTokens,
                'cost' => $this->estimateCost($inputTokens, $outputTokens),
                'duration_ms' => $durationMs,
                'status' => $status,
                'error_message' => $errorMessage,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log AI usage', [
                'error' => $e->getMessage(),
                'action' => $action,
            ]);
        }
    }

    /**
     * Calculate elapsed milliseconds since start time.
     */
    private function elapsedMs(float $startTime): int
    {
        return (int) round((microtime(true) - $startTime) * 1000);
    }
}
