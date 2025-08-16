<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\PersonalAccessTokenResult;
// use Illuminate\Support\Facades\Cookie; // si decides usar cookie

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // 1) Validación
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2) Intento de login (tu vista no manda "remember"; queda false por defecto)
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 3) Proteger sesión contra fixation
        $request->session()->regenerate();

        // 4) Crear el token personal de Passport (¡ANTES de usarlo!)
        /** @var PersonalAccessTokenResult $tokenResult */
        $tokenResult = $request->user()->createToken('web');

        // 5) Guardar SÓLO la cadena del token en sesión (opción A)
        $request->session()->put('api_token', $tokenResult->accessToken);
        // (opcional) guardar el id del token para revocarlo puntualmente en logout:
        $request->session()->put('api_token_id', $tokenResult->token->id);

        // // Opción B: entregarlo por cookie legible por JS
        // Cookie::queue('api_token', $tokenResult->accessToken, 60*24, '/', null, false, false, false, 'Lax');

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request): RedirectResponse
    {
        // 1) Revocar el token actual si guardaste su id en sesión
        if ($user = $request->user()) {
            if ($tokenId = $request->session()->pull('api_token_id')) {
                $user->tokens()->where('id', $tokenId)->delete();
            } else {
                // fallback: borra todos los tokens llamados "web"
                $user->tokens()->where('name', 'web')->delete();
            }
        }

        // 2) Limpiar sesión
        $request->session()->forget('api_token');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3) Redirigir
        return redirect('/login');
    }

    public function create(): \Illuminate\Contracts\View\View
    {
        return view('auth.login');
    }
}