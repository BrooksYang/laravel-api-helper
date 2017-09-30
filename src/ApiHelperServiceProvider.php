<?php

namespace BrooksYang\LaravelApiHelper;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ApiHelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/views', 'api_doc');

        // Publish assets
        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/api_doc'),
        ], 'api-doc');

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
    }

    /**
     * Share Data
     */
    public function viewComposer()
    {
        View::composer(['api_doc::layouts.includes.menu'], function ($view) {
            $view->with('modules', \BrooksYang\LaravelApiHelper\Facades\Doc::modules());
            $view->with('total', \BrooksYang\LaravelApiHelper\Facades\Doc::total());
        });
    }
}
