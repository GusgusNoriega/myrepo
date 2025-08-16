@extends('layouts.admin')

@section('title', 'Seccion 1-2')

@section('content')
    <h2>Vista: Opción 1-2</h2>
    {{-- Imagen única --}}
<input type="hidden" name="featured_image_id" id="featured_image_id"
       value=""
       data-mm="image"            {{-- tipo: image | document | video | audio | '' (todos) --}}
       data-mm-multiple="false"   {{-- "true" para galería --}}
       data-mm-preview="#selector"
       data-mm-title="Selecciona imagen de portada">

{{-- Opcional: si quieres un contenedor de preview propio --}}
<div id="selector" class="mt-2"></div>
{{-- Galería de imágenes --}}
<input type="hidden" name="gallery_ids" id="gallery_ids"
       value=""
       data-mm="image"
       data-mm-multiple="true"
       data-mm-title="Selecciona imágenes de la galería">

{{-- Archivo único (no imagen) --}}
<input type="hidden" name="brochure_id" id="brochure_id"
       value=""
       data-mm="document"
       data-mm-multiple="false"
       data-mm-title="Selecciona el brochure">

{{-- Galería de archivos --}}
<input type="hidden" name="attachments_ids" id="attachments_ids"
       value=""
       data-mm="document"
       data-mm-multiple="true"
       data-mm-title="Selecciona adjuntos">

    <x-media-manager :api-base="url('http://myrepo.test/api/media')" />
@endsection