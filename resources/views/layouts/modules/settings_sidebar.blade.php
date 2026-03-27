<div class="side-nav__devider my-6"></div>
<div class="side-menu__group-title pl-5 mb-2 text-gray-500 uppercase text-xs font-bold tracking-wider">CONFIGURACIÓN</div>

<ul>
    <li>
        @can('menu_configuracion')
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="settings"></i> </div>
            <div class="side-menu__title">
                SISTEMA
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        @endcan

        <ul class="">
            @can('menu_empresa')
            <li>
                <a href="{{ route('settings') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="globe"></i> </div>
                    <div class="side-menu__title"> EMPRESA  </div>
                </a>
            </li>
            @endcan

            @can('menu_roles')
            <li>
                <a href="{{ route('roles') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="shield"></i> </div>
                    <div class="side-menu__title"> ROLES  </div>
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

     <li>
        @can('menu_configuracion')
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="settings"></i> </div>
            <div class="side-menu__title">
                GESTIÓN PROCESAL
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        @endcan

        <ul class="">
            @can('menu_empresa')
            <li>
                <a href="{{ route('materias') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="globe"></i> </div>
                    <div class="side-menu__title"> MATERIAS  </div>
                </a>
            </li>
            @endcan

            @can('menu_roles')
            <li>
                <a href="{{ route('procedimientos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="shield"></i> </div>
                    <div class="side-menu__title"> PROCEDIMIENTOS  </div>
                </a>
            </li>
            @endcan

            @can('menu_asginar_permisos')
            <li>
                <a href="{{ route('asuntos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="lock"></i> </div>
                    <div class="side-menu__title"> ASUNTOS  </div>
                </a>
            </li>
            @endcan
            @can('menu_asginar_permisos')
            <li>
                <a href="{{ route('fases-procesales') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="lock"></i> </div>
                    <div class="side-menu__title"> FASES PROCESALES  </div>
                </a>
            </li>
            @endcan
            @can('menu_asginar_permisos')
            <li>
                <a href="{{ route('estados-procesales') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="lock"></i> </div>
                    <div class="side-menu__title"> EST PROCESALES  </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>



