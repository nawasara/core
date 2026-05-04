<?php

namespace Nawasara\Core;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-core');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishSpatiePermission();

        $this->offerPublishing();

        $this->registerLivewire();

        $this->registerSocialiteProviders();
    }

    /**
     * Hook Socialite custom providers (Keycloak) lewat event listener pattern
     * yang di-pakai socialiteproviders/manager.
     *
     * Listener cuma extend Socialite — credential di-hydrate runtime oleh
     * Nawasara\Core\Services\SsoService dari Vault sebelum panggil driver.
     */
    protected function registerSocialiteProviders(): void
    {
        if (! class_exists(\SocialiteProviders\Manager\SocialiteWasCalled::class)) {
            return;
        }

        Event::listen(\SocialiteProviders\Manager\SocialiteWasCalled::class, [
            \SocialiteProviders\Keycloak\KeycloakExtendSocialite::class,
            'handle',
        ]);
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
            __DIR__.'/../database/migrations/add_auth_type_to_users_table.php.stub' => $this->getMigrationFileName('add_auth_type_to_users_table.php'),
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
