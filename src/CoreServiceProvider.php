<?php

namespace Nawasara\Core;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-core');

        // Register Blade components (prefix: nawasara-core)
        Blade::componentNamespace('Nawasara\\Core\\View\\Components', 'nawasara-core');

        // Alias short prefix: core
        Blade::componentNamespace('Nawasara\\Core\\View\\Components', 'core');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/nawasara.php' => config_path('nawasara.php'),
            base_path('vendor/spatie/laravel-permission/config/permission.php') => config_path('permission.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nawasara-core'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../../vendor/spatie/laravel-permission/database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadSeedsFrom(__DIR__.'/../database/seeders');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara.php', 'nawasara');
        // Register Spatie Permission
        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
    }

    /**
     * Method helper untuk load seeder dari package
     */
    protected function loadSeedsFrom($path)
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $path => database_path('seeders'),
            ], 'nawasara-core-seeds');
        }
    }
}
