<?php

namespace App\Providers;

use App\Services\AIService;
use App\Services\ChatbotService;
use App\Services\RecommendationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The Library Assistant is fully local and rule-based. It has no API
        // credentials, external provider, or network dependency.
        $this->app->singleton(AIService::class);
        $this->app->singleton(RecommendationService::class);

        $this->app->singleton(ChatbotService::class, function ($app) {
            return new ChatbotService(
                $app->make(AIService::class),
                $app->make(RecommendationService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
