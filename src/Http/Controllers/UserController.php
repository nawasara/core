<?php

namespace Nawasara\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('nawasara-core::user', compact('users'));
    }
}
