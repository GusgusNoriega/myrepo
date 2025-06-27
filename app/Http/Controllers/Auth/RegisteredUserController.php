<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /** Solo invitados (sin sesión) */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /** GET /register – muestra formulario (Blade que ya viene con Breeze) */
    public function create()
    {
        return view('auth.register');
    }

    /** POST /register – procesa alta */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        /* Opcional: dispara evento de confirmación */
        event(new Registered($user));

        /* Inicia sesión inmediatamente */
        auth()->login($user);

        /* === Passport: crea token para el nuevo usuario === */
        $token = $user->createToken('web')->accessToken;
        /* Guárdalo para tu JS igual que hiciste en el login */
        $request->session()->put('api_token', $token);

        return redirect(RouteServiceProvider::HOME);
    }
}
