<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Providers;

use App\Services\Recommendation\Contracts\RecommendationProviderContract;
use Illuminate\Support\Facades\Http;
use Smpita\TypeAs\TypeAs;

class GeminiRecommendationProvider extends RecommendationProviderContract
{
    public function name(): string
    {
        return 'gemini';
    }

    public function generate(string $prompt): string
    {
        $apiKey = TypeAs::string(config('services.ai.gemini.api_key', ''));
        $model = TypeAs::string(config('services.ai.gemini.model', 'gemini-2.5-flash'));
        $timeout = TypeAs::int(config('services.ai.timeout', 10));
        $retries = TypeAs::int(config('services.ai.retries', 1));

        $response = Http::timeout($timeout)
            ->retry($retries, 100)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ],
            )
            ->throw()
            ->json('candidates.0.content.parts.0.text');

        return TypeAs::string($response);
    }
}
