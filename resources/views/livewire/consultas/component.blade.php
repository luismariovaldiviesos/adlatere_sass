<div>

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
                <x-search />
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
                                    <th class="border-b-2 whitespace-nowrap">ABOGADO</th>
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
                                            <h6 class="mb-1 font-medium">{{ $con->abogado->name ?? 'Sin asignar' }}</h6>
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
                                            @elseif($con->estado_pago === 'pagada')
                                                <span class="badge bg-primary text-blue">Pagada</span>
                                            @elseif($con->estado_pago === 'no_factura')
                                                <span class="badge bg-secondary text-blue">No se cobra</span>
                                            @else
                                                <span class="badge bg-warning text-blue">Pendiente</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            <div class="flex justify-center gap-1 flex-wrap">
                                                <button class="btn btn-warning text-blue border-0"
                                                    wire:click.prevent="Edit({{ $con->id }})" title="Editar / Atender">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($con->estado_atencion === 'pendiente')
                                                <button class="btn btn-primary text-blue border-0"
                                                    wire:click.prevent="atender({{ $con->id }})" title="Marcar atendida">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                                @if($puedeAdministrar && $con->estado_pago === 'pagada' && !$con->factura_id)
                                                <button class="btn btn-info text-blue border-0"
                                                    wire:click.prevent="enviarFacturacion({{ $con->id }})" title="Enviar a facturación">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </button>
                                                @endif
                                                @if($puedeAdministrar && $con->estado_pago === 'pendiente')
                                                <button class="btn btn-outline-success border-0"
                                                    wire:click.prevent="marcarPagada({{ $con->id }})" title="Marcar pagada">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                                @endif
                                                @if($con->estado_atencion !== 'convertida')
                                                <button class="btn btn-success text-blue border-0"
                                                    wire:click.prevent="convertirAJuicio({{ $con->id }})" title="Convertir en juicio">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                                @endif
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
            <div class="px-5 pb-3 flex flex-wrap gap-4 text-sm">
                <span>Cobrado: <strong>${{ number_format($totales['cobrado'], 2) }}</strong></span>
                <span>Pendiente: <strong>${{ number_format($totales['pendiente'], 2) }}</strong></span>
                <span>Cortesías: <strong>{{ $totales['cortesias'] }}</strong></span>
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

</div>
