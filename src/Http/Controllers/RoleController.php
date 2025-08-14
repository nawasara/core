<?php

namespace Nawasara\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nawasara\Core\Services\RoleService;

class RoleController extends Controller
{
    protected $roles;

    public function __construct(RoleService $roles)
    {
        $this->roles = $roles;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $this->roles->createRole($data['name']);

        return redirect()->back()->with('success', 'Role berhasil dibuat.');
    }
}
