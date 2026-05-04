<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Providers;

use App\Services\Recommendation\Contracts\RecommendationProviderContract;
use Illuminate\Support\Facades\Http;
use Smpita\TypeAs\TypeAs;

class ClaudeRecommendationProvider extends RecommendationProviderContract
{
    public function name(): string
    {
        return 'claude';
    }

    public function generate(string $prompt): string
    {
        $apiKey = TypeAs::string(config('services.ai.claude.api_key', ''));
        $timeout = TypeAs::int(config('services.ai.timeout', 10));
        $retries = TypeAs::int(config('services.ai.retries', 1));
        $model = TypeAs::string(config('services.ai.claude.model', 'claude-3-5-sonnet-latest'));

        $response = Http::timeout($timeout)
            ->retry($retries, 100)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 500,
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ])
            ->throw()
            ->json('content.0.text');

        return TypeAs::string($response);
    }
}
