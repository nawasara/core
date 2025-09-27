<?php

namespace Nawasara\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Nawasara\Core\Constants\Constants;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => Constants::DEFAULT_USERNAME,
            'username' => Constants::DEFAULT_USERNAME,
            'email' => Constants::DEFAULT_EMAIL,
            'password' => bcrypt(Constants::DEFAULT_PASSWORD),
        ]);

        $user->assignRole(Constants::DEFAULT_ROLE);
    }
}
