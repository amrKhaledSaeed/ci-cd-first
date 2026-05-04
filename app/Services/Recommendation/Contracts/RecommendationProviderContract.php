<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Contracts;

abstract class RecommendationProviderContract
{
    abstract public function name(): string;

    abstract public function generate(string $prompt): string;
}
