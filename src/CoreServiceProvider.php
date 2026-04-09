<?php

namespace Nawasara\Core;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role as SpatieRole;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-core');

        $this->publishSpatiePermission();

        $this->offerPublishing();

        $this->registerLivewire();

        $this->switchRoleGate();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara.php', 'nawasara');

        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
        $this->app->register(\Laravel\Fortify\FortifyServiceProvider::class);

        if (config('nawasara.use_fortify', true) && class_exists(\Laravel\Fortify\Fortify::class)) {
            $this->app->register(\Nawasara\Core\FortifyServiceProvider::class);
        }
    }

    protected function publishSpatiePermission(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Only publish if migration doesn't exist yet
        $filesystem = $this->app->make(Filesystem::class);
        $migrationPath = $this->app->databasePath('migrations');
        $existing = $filesystem->glob($migrationPath.'/*_create_permission_tables.php');

        if (empty($existing)) {
            \Illuminate\Support\Facades\Artisan::call('vendor:publish', [
                '--provider' => "Spatie\Permission\PermissionServiceProvider",
            ]);
        }
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/nawasara.php' => config_path('nawasara.php'),
        ], 'nawasara-core:config');

        $this->publishes([
            __DIR__.'/../database/migrations/update_permission_and_users_table.php.stub' => $this->getMigrationFileName('update_permission_and_users_table.php'),
        ], 'nawasara-core:migrations');
    }

    public function registerLivewire(): void
    {
        $namespace = 'Nawasara\\Core\\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = str_replace('/', '\\', $file->getRelativePathname());
            $class = $namespace.'\\'.Str::beforeLast($relativePath, '.php');

            if (class_exists($class)) {
                $alias = 'nawasara-core.'.
                    Str::of($relativePath)
                        ->replace('.php', '')
                        ->replace('\\', '.')
                        ->replace('/', '.')
                        ->explode('.')
                        ->map(fn ($segment) => Str::kebab($segment))
                        ->join('.');

                Livewire::component($alias, $class);
            }
        }
    }

    private function switchRoleGate(): void
    {
        Gate::before(function ($user, $ability) {
            $active = session('active_role');
            if (! $active) {
                return null;
            }

            $role = SpatieRole::where('name', $active)->first();

            if (! $role) {
                return false;
            }

            return $role->hasPermissionTo($ability) ? true : false;
        });
    }

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
