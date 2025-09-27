<?php

namespace Nawasara\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Nawasara\Core\Database\Seeders\RoleSeeder;
use Nawasara\Core\Database\Seeders\UserSeeder;
use Nawasara\Core\Database\Seeders\PermissionSeeder;

class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
