<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;   // Breeze lo usa
use Laravel\Passport\PersonalAccessTokenResult;
use Illuminate\Contracts\View\View;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // === Lógica original de Breeze ===============================
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        // =============================================================

        // === Extra: genera el token de Passport ======================
        

       // 1. Generar token
        $tokenResult = $request->user()->createToken('web');

        // 2. Guardar SÓLO la cadena JWT
        $request->session()->put('api_token', $tokenResult->accessToken);

         $tokenResult = $request->user()->createToken('web');

        /*
        dd([
            'clase'       => get_class($tokenResult),
            'accessToken' => $tokenResult->accessToken ?? 'no accessToken',
            'tokenModel'  => $tokenResult->token->toArray(),
        ]);
        */


       
        // Si prefieres exponerlo al front via cookie JS-legible:
        // Cookie::queue('api_token', $token, 60*24, '/', null, false, false);
        // =============================================================

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /* ---------- LOGOUT ----------------------------------------- */
    public function destroy(Request $request): RedirectResponse
    {
        /** 1) Revocar el/los tokens de Passport
         * ----------------------------------------------------------
         *  – Aquí borramos TODOS los tokens llamados "web" para el
         *    usuario; si prefieres revocar solo uno, guarda el id en
         *    sesión y bórralo por id/hash.
         */
        if ($user = $request->user()) {
            $user->tokens()->where('name', 'web')->delete();
            // ó   Token::where('id', $id)->delete();
        }

        /** 2) Limpiar sesión PHP */
        $request->session()->forget('api_token');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        /** 3) Redirigir */
        return redirect('/login');
    }

   public function create(): View
    {
        
        return view('auth.login'); // ← correcto, por la ruta auth/login.blade.php
    }
}
