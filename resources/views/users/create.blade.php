@extends('layouts.app')   {{-- usa tu layout principal con sidebar --}}

@section('content')
<div class="content">
    <h1 class="mb-4">Crear nuevo usuario</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" class="login-form">
        @csrf

        <div class="field">
            <label for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="email">Correo</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required>
            @error('password') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation" required>
        </div>

        <button class="btn-primary">Guardar</button>
    </form>
</div>
@endsection
