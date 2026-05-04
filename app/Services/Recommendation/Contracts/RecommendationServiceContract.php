<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Contracts;

use App\Data\Recommendation\RecommendationContextData;
use App\Data\Recommendation\RecommendationResultData;

abstract class RecommendationServiceContract
{
    abstract public function recommend(RecommendationContextData $context): RecommendationResultData;
}
