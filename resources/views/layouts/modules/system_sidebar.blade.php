<ul>
    <li>
        @can('menu_configuracion')
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="settings"></i> </div>
            <div class="side-menu__title">
                CONFIGURACIÓN
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        @endcan

        <ul class="">
            @can('menu_empresa')
            <li>
                <a href="{{ route('settings') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="settings"></i> </div>
                    <div class="side-menu__title"> EMPRESA  </div>
                </a>
            </li>
            @endcan

            @can('menu_roles')
            <li>
                <a href="{{ route('roles') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="settings"></i> </div>
                    <div class="side-menu__title"> ROLES  </div>
                </a>
            </li>
            @endcan

            @can('menu_asginar_permisos')
            <li>
                <a href="{{ route('asignar') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="activity"></i> </div>
                    <div class="side-menu__title"> ASIGNAR PERMISOS  </div>
                </a>
            </li>
            @endcan

            @can('menu_usuarios')
            <li>
                <a href="{{ route('users') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="key"></i> </div>
                    <div class="side-menu__title"> USUARIOS  </div>
                </a>
            </li>
            @endcan

            @can('menu_descuentos')
            <li>
                <a href="{{ route('descuentos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="key"></i> </div>
                    <div class="side-menu__title"> DESCUENTOS  </div>
                </a>
            </li>
            @endcan

            @can('menu_impuestos')
            <li>
                <a href="{{ route('impuestos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="key"></i> </div>
                    <div class="side-menu__title"> IMPUESTOS  </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>
