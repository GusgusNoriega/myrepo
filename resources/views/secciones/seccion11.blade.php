@extends('layouts.app')

@section('title', 'Panel — MyRepo')
@section('page_title', 'Dashboard')

{{-- Si quieres breadcrumbs personalizados, descomenta: --}}
{{-- 
@section('breadcrumbs')
  <ol class="flex items-center gap-2">
    <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Inicio</a></li>
    <li class="text-gray-400">/</li>
    <li><a href="#" class="hover:text-gray-700">Analítica</a></li>
    <li class="text-gray-400">/</li>
    <li class="text-gray-700 font-medium" aria-current="page">Dashboard</li>
  </ol>
@endsection
--}}

@section('content')
  {{-- Encabezado de la página --}}
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">@yield('page_title')</h1>
      <p class="text-sm text-gray-500">Contenedores listos para integrar tus vistas.</p>
    </div>
    <div class="flex items-center gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4 4-4M4 21h16"/>
        </svg>
        Exportar
      </button>
      <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14"/>
        </svg>
        Acción
      </button>
    </div>
  </div>

  {{-- Tarjetas KPI --}}
  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="text-sm text-gray-500">Ventas</div>
      <div class="mt-2 text-2xl font-semibold">$12,430</div>
      <div class="mt-2 text-xs text-emerald-600">+8.3% vs. mes anterior</div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="text-sm text-gray-500">Pedidos</div>
      <div class="mt-2 text-2xl font-semibold">342</div>
      <div class="mt-2 text-xs text-emerald-600">+2.1% semanal</div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="text-sm text-gray-500">Clientes</div>
      <div class="mt-2 text-2xl font-semibold">1,289</div>
      <div class="mt-2 text-xs text-red-600">-0.4% mensual</div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <div class="text-sm text-gray-500">Inventario</div>
      <div class="mt-2 text-2xl font-semibold">5,742</div>
      <div class="mt-2 text-xs text-gray-500">Ítems en stock</div>
    </div>
  </section>

  {{-- Grid de contenidos --}}
  <section class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 lg:col-span-2">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold">Contenido principal</h2>
        <button class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm hover:bg-gray-200">Acción</button>
      </div>
      <div class="h-56 rounded-xl border border-dashed border-gray-300 bg-gray-50"></div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4">
      <h2 class="mb-3 text-base font-semibold">Resumen lateral</h2>
      <div class="space-y-3">
        <div class="h-16 rounded-xl border border-dashed border-gray-300 bg-gray-50"></div>
        <div class="h-16 rounded-xl border border-dashed border-gray-300 bg-gray-50"></div>
        <div class="h-16 rounded-xl border border-dashed border-gray-300 bg-gray-50"></div>
      </div>
    </div>
  </section>
@endsection


