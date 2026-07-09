<?php

namespace Nawasara\Core;

use Livewire\Livewire;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Nawasara\AuthPrimitives\Auth\Sudo;

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

        $this->registerSudoLogoutHook();

        $this->registerKeycloakSessionGuard();
    }

    /**
     * Append the Keycloak session-liveness check to the `web` middleware group
     * so it runs for every web route across all packages (not just core). It
     * no-ops for guests and local logins; only SSO sessions with a stored
     * refresh token trigger the periodic realm check.
     */
    protected function registerKeycloakSessionGuard(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('keycloak.session', \Nawasara\Core\Http\Middleware\EnsureKeycloakSession::class);
        $router->pushMiddlewareToGroup('web', \Nawasara\Core\Http\Middleware\EnsureKeycloakSession::class);
    }

    /**
     * Drop the sudo grant whenever the user logs out.
     *
     * Fortify's default logout already calls Session::invalidate(), which
     * implicitly clears the sudo window — so under normal conditions this
     * listener is redundant. It exists as a defence-in-depth for any
     * logout path (a custom controller, an Auth::logout() in a Livewire
     * action, a future package) that fires the Logout event without
     * invalidating the session: without this hook, a re-login within the
     * 15-minute window would silently inherit the prior sudo grant.
     */
    protected function registerSudoLogoutHook(): void
    {
        Event::listen(Logout::class, function () {
            Sudo::forget();

            // Capture the SSO id_token BEFORE Fortify invalidates the session,
            // so KeycloakLogoutResponse can use it as id_token_hint for
            // RP-initiated logout. The Logout event fires inside Auth::logout(),
            // ahead of session()->invalidate().
            if (session()->has('sso.refresh_token')) {
                \Nawasara\Core\Http\Responses\KeycloakLogoutResponse::$wasSso = true;
                \Nawasara\Core\Http\Responses\KeycloakLogoutResponse::$idToken = session('sso.id_token');
            }
        });
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

        $this->aliasSudoPrimitives();
    }

    /**
     * Aliases the old `Nawasara\Core\…` sudo classes to their new homes
     * in `Nawasara\AuthPrimitives\…`. The classes themselves were moved
     * out of core into the standalone primitives package; the aliases
     * keep any third-party or pre-existing import working until the
     * next major when these can be dropped.
     *
     * class_alias is no-op if the alias name already exists (e.g. another
     * provider raced us), so it is safe to call unconditionally.
     */
    protected function aliasSudoPrimitives(): void
    {
        $aliases = [
            \Nawasara\AuthPrimitives\Auth\Sudo::class => \Nawasara\Core\Auth\Sudo::class,
            \Nawasara\AuthPrimitives\Attributes\RequiresSudo::class => \Nawasara\Core\Attributes\RequiresSudo::class,
            \Nawasara\AuthPrimitives\Traits\WithSudo::class => \Nawasara\Core\Traits\WithSudo::class,
            \Nawasara\AuthPrimitives\Http\Middleware\EnsureSudo::class => \Nawasara\Core\Http\Middleware\EnsureSudo::class,
            \Nawasara\AuthPrimitives\Exceptions\SudoRequiredException::class => \Nawasara\Core\Exceptions\SudoRequiredException::class,
        ];

        foreach ($aliases as $real => $legacy) {
            if (! class_exists($legacy, autoload: false) && ! trait_exists($legacy, autoload: false)) {
                // class_alias works for both classes and traits in PHP 8.
                class_alias($real, $legacy);
            }
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
