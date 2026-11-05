<nav class="side-nav">
    @if(!tenant())
        {{-- MENU CENTRAL / ADMINISTRACIÓN DEL SAAS --}}
        <a href="{{ route('central.dashboard', ['domain' => request()->getHost()]) }}" class="intro-x flex items-center pl-5 pt-4">
            <img alt="logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
            <span class="hidden xl:block text-white text-lg ml-3"><span class="font-medium">ADLATERE SaaS</span></span>
        </a>
        <div class="side-nav__devider my-6"></div>
        <ul>
            <li>
                <a href="{{ route('central.dashboard', ['domain' => request()->getHost()]) }}" class="side-menu">
                    <div class="side-menu__icon"><i data-feather="home"></i></div>
                    <div class="side-menu__title">DASHBOARD</div>
                </a>
            </li>
            <li>
                <a href="{{ route('central.plans', ['domain' => request()->getHost()]) }}" class="side-menu">
                    <div class="side-menu__icon"><i data-feather="grid"></i></div>
                    <div class="side-menu__title">PLANES</div>
                </a>
            </li>
            <li>
                <a href="{{ url('/') }}" class="side-menu">
                    <div class="side-menu__icon"><i data-feather="globe"></i></div>
                    <div class="side-menu__title">VER LANDING</div>
                </a>
            </li>
        </ul>
    @else
        {{-- MENU DEL INQUILINO (TENANT) CON PERMISOS --}}
        @can('menu_dashboard')
            <a href="{{ url('dash') }}" class="intro-x flex items-center pl-5 pt-4">
                <img alt="logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                <span class="hidden xl:block text-white text-lg ml-3"><span class="font-medium">DASHBOARD</span></span>
            </a>
            <a href="{{ route('calendario') }}" class="side-menu">
                <div class="side-menu__icon"><i data-feather="calendar"></i></div>
                <div class="side-menu__title">CALENDARIO</div>
            </a>
        @endcan

        {{-- 1. FACTURACIÓN Y FINANZAS --}}
        @can('menu_facturacion')
            @include('layouts.modules.billing_sidebar')
        @endcan

        @can('menu_finanzas')
            {{-- Finanzas dentro de facturación/reportes --}}
        @endcan

        {{-- 2. PERSONAS --}}
        @can('menu_personas')
            @include('layouts.modules.people_sidebar')
        @endcan

        {{-- 3. CATÁLOGO --}}
        @can('menu_catalogo')
            @include('layouts.modules.catalog_sidebar')
        @endcan

        {{-- 4. CONFIGURACIÓN --}}
        @can('menu_configuracion')
            @include('layouts.modules.settings_sidebar')
            @include('layouts.modules.adlatere_sidebar')
        @endcan
    @endif
</nav>
