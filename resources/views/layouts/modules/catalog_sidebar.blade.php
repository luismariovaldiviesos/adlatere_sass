<div class="side-nav__devider my-6"></div>
<div class="side-menu__group-title pl-5 mb-2 text-gray-500 uppercase text-xs font-bold tracking-wider">SERVICIOS A FACTURAR</div>

<ul>
    <li>
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="package"></i> </div>
            <div class="side-menu__title">
                CATÁLOGO
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        <ul class="">
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
            
            @can('menu_descuentos')
            <li>
                <a href="{{ route('descuentos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="percent"></i> </div>
                    <div class="side-menu__title"> DESCUENTOS  </div>
                </a>
            </li>
            @endcan

            @can('menu_impuestos')
            <li>
                <a href="{{ route('impuestos') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="divide-circle"></i> </div>
                    <div class="side-menu__title"> IMPUESTOS  </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>
