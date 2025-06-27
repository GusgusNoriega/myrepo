@extends('layouts.guest')

@section('content')
<div class="login-wrapper">
    <div class="login-box">
        <h1 class="login-title">Crear cuenta</h1>

        @if ($errors->any())
            <ul class="error">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('register') }}" class="login-form">
            @csrf

            <div class="field">
                <label for="name">Nombre</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="field">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" type="password"
                       name="password_confirmation" required>
            </div>

            <button type="submit" class="btn-primary">Registrarme</button>

            <p style="text-align:center;margin-top:1rem;">
                <a href="{{ route('login') }}">¿Ya tienes cuenta? Inicia sesión</a>
            </p>
        </form>
    </div>
</div>
@endsection
