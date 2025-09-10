<?php

namespace Nawasara\Core\Services;

use Spatie\Permission\Models\Permission;

class PermissionService
{
    public static function create($group = null, $permissions = [])
    {
        foreach ($permissions as $item) {
            $groupMask = str_replace(' ', '-', $group);
            $item = str_replace(' ', '-', $item);
            $name = strtolower($groupMask.'.'.$item);
            $payload = [
                'name' => $name,
                'group' => strtolower($group),
            ];

            $permission = Permission::updateOrCreate([
                'name' => $name,
            ], $payload);
        }
    }

    public static function getName($input = null)
    {
        // Memecah string berdasarkan titik
        $parts = explode('.', $input);

        // Mengambil bagian terakhir dari array
        $result = end($parts);

        return $result;
    }
}
