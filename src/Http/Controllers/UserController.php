<?php

namespace Nawasara\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User; // pakai model bawaan Laravel app utama

class UserController extends Controller
{
    /**
     * Tampilkan daftar user.
     */
    public function index()
    {
        echo "HAlooo";
        // $users = User::paginate(10);

        // dd($users);
        // return view('nawasara-core::users.index', compact('users'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }
}
