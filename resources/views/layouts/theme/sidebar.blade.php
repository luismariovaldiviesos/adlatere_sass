<nav class="side-nav">
    {{-- MENU CENTRAL (LANDLORD) --}}
    @if(!tenant())
        <a href="{{ url('/dash') }}" class="intro-x flex items-center pl-5 pt-4">
            <img alt="logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
            <span class="hidden xl:block text-white text-lg ml-3"><span class="font-medium">FACTA SaaS</span> </span>
        </a>
        <div class="side-nav__devider my-6"></div>
        <ul>
            <li>
                <a href="{{ route('central.dashboard', ['domain' => request()->getHost()]) }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="home"></i> </div>
                    <div class="side-menu__title"> DASHBOARD </div>
                </a>
            </li>
            <li>
                <a href="{{ route('central.plans', ['domain' => request()->getHost()]) }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="grid"></i> </div>
                    <div class="side-menu__title"> PLANES </div>
                </a>
            </li>
            <li>
                <a href="https://analytics.google.com/" target="_blank" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="activity"></i> </div>
                    <div class="side-menu__title"> ANALYTICS </div>
                </a>
            </li>
        </ul>
    @else
        {{-- MENU DEL INQUILINO (TENANT) CON PERMISOS --}}
        
        @can('menu_dashboard')
        <a href="{{ url('dash') }}" class="intro-x flex items-center pl-5 pt-4">
            <img alt="logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
            <span class="hidden xl:block text-white text-lg ml-3"><span class="font-medium">DASHBOARD</span> </span>
        </a>
        @endcan

        
       
            
        

        {{-- 1. FACTURACIÓN Y FINANZAS (Operativo + Reportes) --}}
        @can('menu_facturacion')
            @include('layouts.modules.billing_sidebar')
        @endcan

        @can('menu_finanzas')
            {{-- Finanzas could be a separate section if requested later, for now it's mostly inside billing_sidebar or reports --}}
        @endcan

        {{-- 2. PERSONAS (Clientes + Usuarios) --}}
        @can('menu_personas')
            @include('layouts.modules.people_sidebar')
        @endcan

        {{-- 3. CATÁLOGO (Servicios a Facturar) --}}
        @can('menu_catalogo')
            @include('layouts.modules.catalog_sidebar')
        @endcan

        {{-- 4. CONFIGURACIÓN (Sistema) --}}
        @can('menu_configuracion')
            @include('layouts.modules.settings_sidebar')
            @include('layouts.modules.adlatere_sidebar')
        @endcan

    @endif
</nav>
