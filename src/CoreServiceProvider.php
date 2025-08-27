<?php

namespace Nawasara\Core;

use Livewire\Livewire;
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

        // Prefix khusus untuk Livewire components di package ini
        Livewire::component('nawasara-core.utils.loading', \Nawasara\Core\Livewire\Utils\Loading::class);
        Livewire::component('nawasara-core.examples.demo-modal', \Nawasara\Core\Livewire\Examples\DemoModal::class);

        // Dynamic menu loader
        $menus = [];
        
        // Scan vendor nawasara/*/config/menu.php
        foreach (glob(base_path('vendor/nawasara/*/config/menu.php')) as $menuPath) {
            $menus = array_merge($menus, require $menuPath);
        }
        app()->instance('nawasara.menu', $menus);

        // Publish config
        $this->publishes([
            __DIR__.'/../config/nawasara.php' => config_path('nawasara.php'),
            base_path('vendor/spatie/laravel-permission/config/permission.php') => config_path('permission.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nawasara-core'),
        ], 'views');

        // Publish assets ke public Laravel root
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/nawasara-core'),
        ], 'public');

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
