<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\UtilityService;
use App\Support\AuditContext;
use App\Support\AuditLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(UtilityService::class, function ($app) {
            return new UtilityService();
        });

        $this->app->singleton(AuditContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $view->with('authUser', Auth::user());
        });

        $this->registerAuditLogging();
    }

    /**
     * Captures every Eloquent create/update/delete app-wide for the audit log,
     * with zero changes needed to any model file.
     */
    private function registerAuditLogging(): void
    {
        Event::listen('eloquent.created: *', fn ($event, $data) => AuditLogger::record('created', $data[0] ?? null));
        Event::listen('eloquent.updated: *', fn ($event, $data) => AuditLogger::record('updated', $data[0] ?? null));
        Event::listen('eloquent.deleted: *', fn ($event, $data) => AuditLogger::record('deleted', $data[0] ?? null));
    }
}
