<?php

namespace Nawasara\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role admin ada
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Buat user admin default jika belum ada
        $user = User::firstOrCreate(
            ['email' => 'admin@nawasara.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Ganti di production
            ]
        );

        // Assign role admin ke user
        if (! $user->hasRole('admin')) {
            $user->assignRole($adminRole);
        }
    }
}
