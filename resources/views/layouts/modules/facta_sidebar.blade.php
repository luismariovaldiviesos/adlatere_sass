@can('menu_dashboard')
<a href="{{ url('dash') }}" class="intro-x flex items-center pl-5 pt-4">
    <img alt="logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
    <span class="hidden xl:block text-white text-lg ml-3"><span class="font-medium">VENTAS</span> </span>
</a>
@endcan

<div class="side-nav__devider my-6"></div>

<ul>
    @can('menu_categorias')
    <li>
        <a href="{{ url('categories') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="layers"></i> </div>
            <div class="side-menu__title"> CATEGORIAS  </div>
        </a>
    </li>
    @endcan

    @can('menu_productos')
    <li>
        <a href="{{ route('products') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="coffee"></i> </div>
            <div class="side-menu__title"> PRODUCTOS  </div>
        </a>
    </li>
    @endcan

    @can('menu_facturar')
    <li>
        <a href="{{ route('facturas') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="dollar-sign"></i> </div>
            <div class="side-menu__title"> FACTURAR  </div>
        </a>
    </li>
    @endcan

    @can('menu_cajas')
    <li>
        <a href="{{ route('cajas') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="shopping-cart"></i> </div>
            <div class="side-menu__title"> CAJAS  </div>
        </a>
    </li>
    @endcan

    @can('menu_arqueos')
    <li>
        <a href="{{ route('arqueos') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="edit"></i> </div>
            <div class="side-menu__title"> ARQUEOS  </div>
        </a>
    </li>
    @endcan
    
    @can('menu_clientes')
    <li>
        <a href="{{ route('customers') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="users"></i> </div>
            <div class="side-menu__title"> CLIENTES  </div>
        </a>
    </li>
    @endcan
</ul>
