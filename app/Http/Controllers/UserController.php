<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** Sólo usuarios autenticados pueden acceder */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* GET /users/create */
    public function create()
    {
        return view('users.create');
    }

    /* POST /users */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:6|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Usuario creado correctamente');
    }
}
