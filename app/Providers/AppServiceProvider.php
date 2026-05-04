<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Cart\PersistedCartService;
use App\Services\Recommendation\Contracts\RecommendationServiceContract;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Smpita\TypeAs\TypeAs;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RecommendationServiceContract::class, RecommendationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();

        Vite::useAggressivePrefetching();
        Vite::macro('image', fn (string $asset) => TypeAs::class(\Illuminate\Foundation\Vite::class, $this)->asset("resources/images/{$asset}"));

        Date::use(CarbonImmutable::class);

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;

            app(PersistedCartService::class)
                ->mergeFromSessionForUser($user instanceof User ? $user : null);
        });
    }
}
