<?php

namespace Nawasara\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        self::role();
    }
    
    public function role()
    {
        $roles = ['super-admin', 'user'];
        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role], ['name' => $role]);
        }
    }
}
