<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Strict mode: prevent lazy loading in non-production environments
        // to catch N+1 query issues during development.
        Model::preventLazyLoading(! app()->isProduction());

        // Strict mode: prevent silently discarding attributes that are not
        // in the fillable array or are guarded, helping catch typos early.
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
    }
}
