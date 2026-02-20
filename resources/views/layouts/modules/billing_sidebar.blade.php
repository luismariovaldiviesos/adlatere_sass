<div class="side-nav__devider my-6"></div>
<div class="side-menu__group-title pl-5 mb-2 text-gray-500 uppercase text-xs font-bold tracking-wider">FACTURACIÓN Y CAJA</div>

<ul>
    @can('menu_facturar')
    <li>
        <a href="{{ route('facturas') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="dollar-sign"></i> </div>
            <div class="side-menu__title"> FACTURAR  </div>
        </a>
    </li>
    @endcan

    <li>
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="shopping-cart"></i> </div>
            <div class="side-menu__title">
                OPERACIONES
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        <ul class="">

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
        </ul>
    </li>

    <li>
        <a href="javascript:;" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>
            <div class="side-menu__title">
                DOCUMENTOS
                <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
            </div>
        </a>
        <ul class="">
            @can('menu_reprocesar')
            <li>
                <a href="{{ route('reprocesar') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="refresh-cw"></i> </div>
                    <div class="side-menu__title"> 
                        REPROCESAR SRI
                        @livewire('reprocesar-count')
                    </div>
                </a>
            </li>
            @endcan

            @can('menu_facturas_emitidas')
            <li>
                <a href="{{ route('listadofacturas') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>
                    <div class="side-menu__title">FACTURAS EMITIDAS</div>
                </a>
            </li>
            @endcan

            @can('menu_faturas_anuladas')
            <li>
                <a href="{{ route('deletedlist') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="trash-2"></i> </div>
                    <div class="side-menu__title">FACTURAS ANULADAS
                        <span class="badge badge-danger ml-2">
                        </span>
                    </div>
                </a>
            </li>
            @endcan

            @can('menu_notas_credito')
            <li>
                <a href="{{ route('notascredito') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="file-minus"></i> </div>
                    <div class="side-menu__title">NOTAS DE CRÉDITO</div>
                </a>
            </li>
            @endcan

            @can('menu_reportes')
            <li>
                <a href="{{ route('reports') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="pie-chart"></i> </div>
                    <div class="side-menu__title"> REPORTES  </div>
                </a>
            </li>
            @endcan

            @can('menu_ventas_diarias')
            <li>
                <a href="{{ route('diario') }}" class="side-menu">
                    <div class="side-menu__icon"> <i data-feather="activity"></i> </div>
                    <div class="side-menu__title"> VENTA DIARIA  </div>
                </a>
            </li>
            @endcan
        </ul>
    </li>
</ul>
