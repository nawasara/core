<?php

namespace Nawasara\Core;

use Livewire\Livewire;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-core');
        
        $this->publishSpatiePermission();
        
        $this->offerPublishing();

        $this->registerBlade();

        $this->registerLivewire();
        
        $this->menuLoader();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara.php', 'nawasara');
        // Register Spatie Permission
        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
    }

    protected function publishSpatiePermission(): void
    {
        // publish otomatis saat pertama kali install
        if ($this->app->runningInConsole()) {
            Artisan::call('vendor:publish', [
                '--provider' => "Spatie\Permission\PermissionServiceProvider",
                '--force' => true, // hati-hati overwrite
            ]);
        }
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/nawasara.php' => config_path('nawasara.php'),
        ], 'nawasara-core:config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nawasara-core'),
        ], 'nawasara-core:views');

        // Publish assets ke public Laravel root
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/nawasara-core'),
        ], 'nawasara-core:public');

        $this->publishes([
            __DIR__.'/../database/migrations/update_permission_and_users_table.php.stub' => $this->getMigrationFileName('update_permission_and_users_table.php'),
        ], 'nawasara-core:migrations');

    }
    
    public function menuLoader(): void{
        $menus = [];
        
        // Scan vendor nawasara/*/config/menu.php
        foreach (glob(base_path('vendor/nawasara/*/config/menu.php')) as $menuPath) {
            $menuConfig = require $menuPath;
            if (is_array($menuConfig)) {
                $menus = array_merge($menus, $menuConfig);
            }
        }
        app()->instance('nawasara.menu', $menus);

    }

    public function registerLivewire(): void
    {
        Livewire::component('nawasara-core.utils.loading', \Nawasara\Core\Livewire\Utils\Loading::class);
        Livewire::component('nawasara-core.examples.demo-modal', \Nawasara\Core\Livewire\Examples\DemoModal::class);
        Livewire::component('nawasara-core.components.universal-modal', \Nawasara\Core\Livewire\Components\UniversalModal::class);
    }

    private function registerBlade(): void
    {
        Blade::componentNamespace('Nawasara\\Core\\View\\Components', 'nawasara-core');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
