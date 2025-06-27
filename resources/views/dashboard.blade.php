@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="layout">
    {{-- Barra lateral --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h2>Mi Sistema</h2>
        </div>

        <nav class="menu">
            <ul>
                {{-- === Sección 1 === --}}
                <li class="menu-item">
                    <button class="accordion" data-section="sec1">Sección 1</button>
                    <ul class="submenu">
                        <li>
                            <a  href="{{ route('seccion11') }}"
                                class="{{ request()->routeIs('seccion11') ? 'active' : '' }}"
                            >Opción 1-1</a></li>
                        <li>
                            <a  href="{{ route('seccion12') }}"
                                class="{{ request()->routeIs('seccion12') ? 'active' : '' }}"
                            >Opción 1-2</a></li>
                    </ul>
                </li>

                {{-- === Sección 2 === --}}
                <li class="menu-item">
                    <button class="accordion" data-section="sec2">Sección 2</button>
                    <ul class="submenu">
                        <li>
                            <a  href="{{ route('seccion21') }}"
                                class="{{ request()->routeIs('seccion21') ? 'active' : '' }}"
                            >Opción 2-1</a></li>
                        <li>
                            <a  href="{{ route('seccion22') }}"
                                class="{{ request()->routeIs('seccion22') ? 'active' : '' }}"
                            >Opción 2-2</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </aside>

    {{-- Contenido principal --}}
    <main class="main">
        <header class="main-header">
            <button id="sidebarToggle" class="hamburger" aria-label="Mostrar/ocultar menú">
                &#9776;
            </button>
            <h1>@yield('title')</h1>
        </header>

        <section class="content">
            <p>Contenido principal…</p>
        </section>

        <footer class="main-footer">
            <small>© 2025 Mi Sistema</small>
        </footer>
    </main>
</div>
@endsection

