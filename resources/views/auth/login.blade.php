@extends('layouts.guest')

@section('content')
<div class="login-wrapper">
    <div class="login-box">
        <h1 class="login-title">Iniciar sesión</h1>

        @if ($errors->any())
            <ul class="error">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Entrar</button>
        </form>
    </div>
</div>
@endsection
