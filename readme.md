# Nawasara Core

The core package of the Nawasara superapp framework. Provides authentication, role-based access control, branding configuration, and a `RespectsActiveRole` trait that scopes permission checks to the user's currently active role.

## Features

- **Authentication via Laravel Fortify** — login, password reset, two-factor, and email verification, configurable through `nawasara.use_fortify`
- **Role & permission via Spatie** — auto-publishes the Spatie permission migration and registers seeders
- **Active role enforcement** — when a multi-role user "switches" to a specific role from the topbar, all permission checks are scoped to that single role's grants instead of the union of every role they hold
- **Branding settings** — application name, subtitle, logo (light & dark), and favicon, manageable from `/admin/branding`
- **User & role management UI** — Livewire pages for managing users and roles
- **Helpers** — small global helpers in `src/Helpers/functions.php`

## Installation

```bash
composer require nawasara/core
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan db:seed --class="Nawasara\Core\Database\Seeders\PermissionSeeder" --force
```

## RespectsActiveRole — applying the trait

Apply the trait on top of Spatie's `HasRoles` in your `App\Models\User`:

```php
use Nawasara\Core\Traits\RespectsActiveRole;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, RespectsActiveRole {
        RespectsActiveRole::hasPermissionTo insteadof HasRoles;
    }
}
```

After this, calling `$user->switchRole('operator')` (or setting `session('active_role', 'operator')`) limits all subsequent permission checks to the `operator` role's permissions for the rest of the session. Direct user-grant permissions still apply.

## Pages

| Route | Permission |
|-------|-----------|
| `/admin/users` | `nawasara-core.user.view` |
| `/admin/roles` | `nawasara-core.role.view` |
| `/admin/branding` | `nawasara-core.branding.manage` |

## Author

**Pringgo J. Saputro** &lt;odyinggo@gmail.com&gt;

## License

MIT
