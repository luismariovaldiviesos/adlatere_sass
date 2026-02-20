<div class="side-nav__devider my-6"></div>
<div class="side-menu__group-title pl-5 mb-2 text-gray-500 uppercase text-xs font-bold tracking-wider">GESTIÓN DE PERSONAS</div>

<ul>
    <li>
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="users"></i> </div>
            <div class="side-menu__title">
                PERSONAS
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        <ul class="">
            @can('menu_clientes')
            <li>
                <a href="{{ route('customers') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="users"></i> </div>
                    <div class="side-menu__title"> CLIENTES  </div>
                </a>
            </li>
            @endcan

            @can('menu_usuarios')
            <li>
                <a href="{{ route('users') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="user-check"></i> </div>
                    <div class="side-menu__title"> USUARIOS SISTEMA </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>
