<?php

namespace BrooksYang\LaravelApiHelper;

use BrooksYang\LaravelApiHelper\Middleware\RequestCounter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ApiHelperServiceProvider extends ServiceProvider
{
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'request.counter' => RequestCounter::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/config/api-helper.php' => config_path('api-helper.php'),
        ], 'api-helper');

        // Load routes
        if (config('api-helper.api_doc', true) !== false) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/views', 'api_helper');

        // Publish assets
        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/api_helper'),
        ], 'api-helper');

        // View Share
        $this->viewComposer();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Register the application bindings.
        $this->app->bind('doc', function () {
            return new Doc();
        });

        // Default Package Configuration
        $this->mergeConfigFrom(
            __DIR__.'/config/api-helper.php', 'api-helper'
        );

        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }
    }

    /**
     * Share Data
     */
    public function viewComposer()
    {
        View::composer(['api_helper::layouts.includes.menu'], function ($view) {
            $view->with('modules', \BrooksYang\LaravelApiHelper\Facades\Doc::modules());
            $view->with('total', \BrooksYang\LaravelApiHelper\Facades\Doc::total());
        });
    }
}
