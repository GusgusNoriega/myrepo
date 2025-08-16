@extends('layouts.admin')

@section('title', 'Seccion 1-1')

@section('content')
    <h2>Vista: Opción 1-1</h2>
    <p>Aquí va el contenido específico de esta página (tablas, formularios, etc.).</p>
    

    @if(session('ok'))
        <p class="text-green-600">{{ session('ok') }}</p>
    @endif
@endsection


