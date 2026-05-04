<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Providers;

use App\Services\Recommendation\Contracts\RecommendationProviderContract;
use Illuminate\Support\Facades\Http;
use Smpita\TypeAs\TypeAs;

class OpenAiRecommendationProvider extends RecommendationProviderContract
{
    public function name(): string
    {
        return 'openai';
    }

    public function generate(string $prompt): string
    {
        $apiKey = TypeAs::string(config('services.ai.openai.api_key', ''));
        $timeout = TypeAs::int(config('services.ai.timeout', 10));
        $retries = TypeAs::int(config('services.ai.retries', 1));
        $model = TypeAs::string(config('services.ai.openai.model', 'gpt-4o-mini'));

        $response = Http::timeout($timeout)
            ->retry($retries, 100)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'system', 'content' => 'Return only valid JSON array output.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ])
            ->throw()
            ->json('choices.0.message.content');

        return TypeAs::string($response);
    }
}
