@extends('layouts.web')

@section('title', 'Iniciar sesión — Tu Tienda Online')
@section('meta_description', 'Accede a tu panel de control.')

@section('content')
  <div class="mx-auto max-w-md">
    <div class="text-center mb-6">
      <h1 class="text-3xl sm:text-4xl font-extrabold">Iniciar sesión</h1>
      <p class="mt-2 text-slate-600">Accede a tu panel de control.</p>

      @if ($errors->any())
        <ul class="text-sm text-red-600 mt-3 space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      @endif
    </div>

    <div class="rounded-2xl border border-slate-200 p-6 bg-white">
      <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
          <label for="email" class="text-sm text-slate-600">Email</label>
          <input id="email" name="email" type="email" required autofocus
                 autocomplete="email"
                 class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                 placeholder="tu@email.com"
                 value="{{ old('email') }}">
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="text-sm text-slate-600">Contraseña</label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-xs text-brand-700 hover:underline">
                ¿Olvidaste tu contraseña?
              </a>
            @endif
          </div>

          <div class="mt-1 relative">
            <input id="password" name="password" type="password" required
                   autocomplete="current-password"
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 pr-10"
                   placeholder="••••••••">
            <button type="button" id="togglePwd"
                    class="absolute inset-y-0 right-2 my-auto text-slate-500 text-sm">
              Mostrar
            </button>
          </div>
          @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm">
            <input id="remember" name="remember" type="checkbox" class="rounded border-slate-300" @checked(old('remember'))>
            <span>Recordarme</span>
          </label>
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="text-xs text-slate-600 hover:underline">
              ¿No tienes cuenta? Crear cuenta
            </a>
          @endif
        </div>

        <button type="submit"
                class="w-full px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800">
          Entrar
        </button>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
    // Toggle password (esto NO bloquea el submit)
    document.getElementById('togglePwd')?.addEventListener('click', function () {
      const input = document.getElementById('password');
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      this.textContent = isPwd ? 'Ocultar' : 'Mostrar';
    });
  </script>
  @endpush
@endsection