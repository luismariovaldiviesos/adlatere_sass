<div class="side-nav__devider my-6"></div>
<div class="side-menu__group-title pl-5 mb-2 text-gray-500 uppercase text-xs font-bold tracking-wider">LUGARES</div>

<ul>
    <li>
        @can('menu_configuracion')
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="map-pin"></i> </div>
                <div class="side-menu__title">
                    SITIOS
                    <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
                </div>
        </a>
        @endcan

        <ul class="">
            @can('menu_empresa')
            <li>
                <a href="{{ route('provincias') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="globe"></i> </div>
                    <div class="side-menu__title"> PROVINCIAS  </div>
                </a>
            </li>
            @endcan

            @can('menu_roles')
            <li>
                <a href="{{ route('cantones') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="shield"></i> </div>
                    <div class="side-menu__title"> CANTONES  </div>
                </a>
            </li>
            @endcan

            @can('menu_asginar_permisos')
            <li>
                <a href="{{ route('asignar') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="lock"></i> </div>
                    <div class="side-menu__title"> PERMISOS  </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>
