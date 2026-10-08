<div>

    @if($puedeEntrar)

    @if (!$form)

        <div class="intro-y col-span-12">

            <div class="intro-y box">

            <h2 class="text-lg font-medium text-center text-them-1 py-4">
                @if($esPanelAbogado) Mis consultas asignadas @else {{ $componentName }} @endif
            </h2>

                @if($esPanelAbogado)
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2 p-4">
                    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml:0">
                        <div class="relative text-gray-700 dark:text-gray-300">
                            <input wire:model="search" type="text" class="form-control box placeholder-theme-13 w-full sm:w-auto" placeholder="Buscar...">
                            <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 rigth-0 fas fa-search"></i>
                        </div>
                    </div>
                </div>
                @else
                    @can('crear_consulta')
                    <x-search />
                    @else
                    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2 p-4">
                        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml:0">
                            <div class="relative text-gray-700 dark:text-gray-300">
                                <input wire:model="search" type="text" class="form-control box placeholder-theme-13 w-full sm:w-auto" placeholder="Buscar...">
                                <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 rigth-0 fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    @endcan
                @endif

                {{-- FILTROS DEL PANEL --}}
                <div class="px-5 pb-2 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="form-label text-xs">Atención</label>
                        <select wire:model="filtroAtencion" class="form-select w-auto">
                            <option value="todos">Todas</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="atendida">Atendidas</option>
                            <option value="convertida">Convertidas en juicio</option>
                        </select>
                    </div>
                    @if($puedeAdministrar)
                    <div>
                        <label class="form-label text-xs">Facturación</label>
                        <select wire:model="filtroPago" class="form-select w-auto">
                            <option value="todos">Todas</option>
                            <option value="pendiente">Pendientes de pago</option>
                            <option value="pagada">Pagadas</option>
                            <option value="en_facturacion">En facturación</option>
                            <option value="facturada">Facturadas</option>
                            <option value="no_factura">No se cobra</option>
                        </select>
                    </div>
                    @endif
                    @if(auth()->user()->profile === 'Admin' && !$esPanelAbogado)
                    <div class="flex items-center gap-2 pb-2">
                        <input type="checkbox" wire:model="soloMias" id="soloMias" class="form-check-input">
                        <label for="soloMias" class="text-xs">Solo mis asignadas</label>
                    </div>
                    @endif
                </div>

            <div class="p-5">
                <div class="preview">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="text-theme-1">
                                    <th class="border-b-2 whitespace-nowrap">CLIENTE</th>
                                    <th class="border-b-2 whitespace-nowrap">ASUNTO</th>
                                    <th class="border-b-2 whitespace-nowrap">@if($esPanelAbogado) NOTAS @else ABOGADO @endif</th>
                                    @if($puedeAdministrar)<th class="border-b-2 whitespace-nowrap">COSTO</th>@endif
                                    <th class="border-b-2 whitespace-nowrap">ATENCIÓN</th>
                                    @if($puedeAdministrar)<th class="border-b-2 whitespace-nowrap">PAGO</th>@endif
                                    <th class="border-b-2 whitespace-nowrap text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($consultas as $con)
                                    <tr class="{{ $loop->index % 2 > 0 ? 'bg-gray-200' : '' }}">
                                        <td>
                                            <h6 class="mb-1 font-medium">{{ $con->customer->businame ?? '—' }}</h6>
                                            <small class="text-gray-500">{{ $con->customer->valueidenti ?? '' }}</small>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 font-medium">{{ $con->asunto->nombre ?? '—' }}</h6>
                                            <small class="text-gray-500">{{ $con->asunto->procedimiento->materia->nombre ?? '' }}</small>
                                        </td>
                                        <td>
                                            @if($esPanelAbogado)
                                                @php $txtNotas = trim(strip_tags($con->notas ?? '')); @endphp
                                                @if($txtNotas !== '')
                                                    <span title="{{ $txtNotas }}" class="text-sm text-gray-700">
                                                        {{ \Illuminate\Support\Str::limit($txtNotas, 80) }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Sin notas</span>
                                                @endif
                                            @else
                                                <h6 class="mb-1 font-medium">{{ $con->abogado->name ?? 'Sin asignar' }}</h6>
                                            @endif
                                        </td>
                                        @if($puedeAdministrar)
                                        <td>
                                            <h6 class="mb-1 font-medium">${{ number_format($con->costo, 2) }}</h6>
                                        </td>
                                        @endif
                                        <td>
                                            @if($con->estado_atencion === 'pendiente')
                                                <span class="badge bg-warning text-blue">Pendiente</span>
                                            @elseif($con->estado_atencion === 'atendida')
                                                <span class="badge bg-primary text-blue">Atendida</span>
                                            @else
                                                <span class="badge bg-success text-blue">Juicio #{{ $con->juicio_id }}</span>
                                            @endif
                                        </td>
                                        @if($puedeAdministrar)
                                        <td>
                                            @if($con->estado_pago === 'facturada')
                                                <span class="badge bg-success text-blue">Facturada</span>
                                            @elseif($con->estado_pago === 'en_facturacion')
                                                <span class="badge bg-info text-blue">En facturación</span>
                                            @elseif($con->estado_pago === 'pagada')
                                                <span class="badge bg-primary text-blue">Pagada</span>
                                            @elseif($con->estado_pago === 'no_factura')
                                                <span class="badge bg-secondary text-blue">No se cobra</span>
                                            @else
                                                <span class="badge bg-warning text-blue">Pendiente</span>
                                            @endif
                                            @if($con->comprobante_ruta)
                                            <div class="mt-1">
                                                <button class="btn btn-outline-primary btn-sm"
                                                    wire:click.prevent="descargarComprobante({{ $con->id }})" title="Descargar comprobante">
                                                    <i class="fas fa-download"></i> Comprobante
                                                </button>
                                            </div>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            <div class="flex justify-center gap-1 flex-wrap">
                                                @if(auth()->user()->can('editar_datos_consulta') || auth()->user()->can('guardar_notas_consulta'))
                                                <button class="btn btn-warning text-blue border-0"
                                                    wire:click.prevent="Edit({{ $con->id }})" title="Editar / Atender">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @endif
                                                @can('atender_consulta')
                                                @if($con->estado_atencion === 'pendiente')
                                                <button class="btn btn-primary text-blue border-0"
                                                    wire:click.prevent="atender({{ $con->id }})" title="Marcar atendida">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                @can('facturar_consulta')
                                                @if($puedeAdministrar && $con->estado_pago === 'pagada' && !$con->factura_id)
                                                <button class="btn btn-info text-blue border-0"
                                                    wire:click.prevent="enviarFacturacion({{ $con->id }})" title="Crear borrador de factura">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </button>
                                                @endif
                                                @if($puedeAdministrar && $con->estado_pago === 'en_facturacion' && $con->factura_id)
                                                <button class="btn btn-success text-white border-0"
                                                    wire:click.prevent="emitirFacturaConsulta({{ $con->id }})" title="Emitir borrador al SRI">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                @can('marcar_pagada_consulta')
                                                @if($puedeAdministrar && $con->estado_pago === 'pendiente')
                                                <button class="btn btn-outline-success border-0"
                                                    wire:click.prevent="marcarPagada({{ $con->id }})" title="Marcar pagada">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                @can('convertir_consulta')
                                                @if($con->estado_atencion !== 'convertida')
                                                <button class="btn btn-success text-blue border-0"
                                                    wire:click.prevent="convertirAJuicio({{ $con->id }})" title="Convertir en juicio">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200">
                                        <td colspan="7"><h6 class="text-center">NO HAY CONSULTAS</h6></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($puedeAdministrar)
            <div class="px-5 pb-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-6">
                    <div class="box p-4 text-center border-l-4 border-green-500">
                        <div class="text-xs text-gray-500 uppercase">Facturadas</div>
                        <div class="text-2xl font-bold">{{ $totales['facturadas_n'] }}</div>
                        <div class="text-lg font-semibold text-green-600">${{ number_format($totales['facturadas_valor'], 2) }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="box p-4 text-center border-l-4 border-yellow-500">
                        <div class="text-xs text-gray-500 uppercase">No facturadas</div>
                        <div class="text-2xl font-bold">{{ $totales['nofacturadas_n'] }}</div>
                        <div class="text-lg font-semibold text-yellow-600">${{ number_format($totales['nofacturadas_valor'], 2) }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-spam-12 p-5">
                {{ $consultas->links() }}
            </div>

            </div>
        </div>
    @else

        @include('livewire.consultas.form')

    @endif

    @else
    <div class="alert alert-danger" role="alert">
        <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
    </div>
    @endif

</div>
