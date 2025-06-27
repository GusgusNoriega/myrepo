@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ $documento->titulo }}</h1>

    
    @if($documento->hasMedia('imagenes'))
        @foreach($documento->getMedia('imagenes') as $media)
            <p>Archivo real: {{ $media->file_name }} | ID media: {{ $media->id }}</p>
            <img class="imagen-prueba" src="{{ $media->getUrl() }}" alt="" class="mb-4 rounded shadow">
        @endforeach
    @else
        <p>No hay imágenes asociadas a este documento</p>
    @endif
    <pre>ID desde el modelo: {{ $documento->id }}</pre>
    <pre>{{ $documento->getFirstMediaUrl('imagenes') }}</pre>

    {{-- archivo --}}
    @php $file = $documento->getFirstMedia('archivos'); @endphp
    @if($file)
        <a href="{{ route('media.download', $file) }}" class="text-indigo-600 underline">
            Descargar {{ $file->file_name }}
        </a>
    @endif
@endsection

