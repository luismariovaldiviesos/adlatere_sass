<ul>
    @can('menu_reprocesar')
    <li>
        <a href="{{ route('reprocesar') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="edit"></i> </div>
            <div class="side-menu__title"> 
                REPROCESAR 
                @livewire('reprocesar-count')
            </div>
        </a>
    </li>
    @endcan

    @can('menu_facturas_emitidas')
    <li>
        <a href="{{ route('listadofacturas') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="eye"></i> </div>
            <div class="side-menu__title">FACTURAS EMITIDAS</div>
        </a>
    </li>
    @endcan

    @can('menu_faturas_anuladas')
    <li>
        <a href="{{ route('deletedlist') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="eye"></i> </div>
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
            <div class="side-menu__icon"> <i data-feather="eye"></i> </div>
            <div class="side-menu__title">NOTAS DE CRÉDITO</div>
        </a>
    </li>
    @endcan
    
    <div class="side-nav__devider my-6"></div>

    @can('menu_reportes')
    <li>
        <a href="{{ route('reports') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="calendar"></i> </div>
            <div class="side-menu__title"> REPORTES  </div>
        </a>
    </li>
    @endcan

    @can('menu_ventas_diarias')
    <li>
        <a href="{{ route('diario') }}" class="side-menu">
            <div class="side-menu__icon"> <i data-feather="eye"></i> </div>
            <div class="side-menu__title"> VENTA DIARIA  </div>
        </a>
    </li>
    @endcan
</ul>
