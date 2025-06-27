@extends('layouts.admin')

@section('title', 'Seccion 1-1')

@section('content')
    <h2>Vista: Opción 1-1</h2>
    <p>Aquí va el contenido específico de esta página (tablas, formularios, etc.).</p>
    <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="text" name="titulo" placeholder="Título" class="border p-2 w-full">
        <input type="file" name="imagen" class="border p-2 w-full">
        <input type="file" name="archivo" class="border p-2 w-full">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded">Guardar</button>
    </form>

    @if(session('ok'))
        <p class="text-green-600">{{ session('ok') }}</p>
    @endif
@endsection


