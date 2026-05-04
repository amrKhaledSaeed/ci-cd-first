<?php

declare(strict_types=1);

namespace App\Repositories\Analytics;

use App\Models\StoreAnalyticsEvent;
use App\Repositories\BaseRepository;

/**
 * @extends BaseRepository<StoreAnalyticsEvent>
 */
class StoreAnalyticsEventRepository extends BaseRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function track(array $attributes): void
    {
        $this->create($attributes);
    }

    protected function model(): StoreAnalyticsEvent
    {
        return new StoreAnalyticsEvent;
    }
}
