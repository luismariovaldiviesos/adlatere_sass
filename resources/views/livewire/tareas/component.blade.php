<div>

    @if(!$puedeEntrar)
        <div class="alert alert-danger" role="alert">
            <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
        </div>
    @elseif (!$form)

        <div class="intro-y col-span-12">

            <div class="intro-y box">

            <h2 class="text-lg font-medium text-center text-them-1 py-4">
                {{ $componentName }}
            </h2>

                @can('crear_tarea')
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

                {{-- FILTROS --}}
                <div class="px-5 pb-2 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="form-label text-xs">Estado</label>
                        <select wire:model="filtroEstado" class="form-select w-auto">
                            <option value="abiertas">Abiertas</option>
                            <option value="todas">Todas</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="completada">Completadas</option>
                            <option value="convertida">Convertidas</option>
                            <option value="cancelada">Canceladas</option>
                            <option value="vencidas">Vencidas</option>
                        </select>
                    </div>
                    @if($puedeGestionar)
                    <div class="flex items-center gap-2 pb-2">
                        <input type="checkbox" wire:model="soloMias" id="soloMiasTareas" class="form-check-input">
                        <label for="soloMiasTareas" class="text-xs">Solo mis asignadas</label>
                    </div>
                    @endif
                </div>

                {{-- PANEL CONVERTIR (selector de tipo de actividad) --}}
                @if($convertir_id)
                <div class="mx-5 mb-2 p-4 rounded-lg border border-green-300 bg-green-50 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="form-label text-xs">Convertir tarea #{{ $convertir_id }} en actividad tipo:</label>
                        <select wire:model.defer="conv_tipo_actividad_id" class="form-select w-auto">
                            <option value="">Seleccione...</option>
                            @foreach($tiposActividades as $t)
                            <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                        @error('conv_tipo_actividad_id') <span class="text-red-600 text-xs block">{{ $message }}</span> @enderror
                    </div>
                    <button wire:click.prevent="confirmarConvertir" class="btn btn-success text-white">Confirmar</button>
                    <button wire:click.prevent="cancelarConvertir" class="btn btn-outline-secondary">Cancelar</button>
                </div>
                @endif

            <div class="p-5">
                <div class="preview">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="text-theme-1">
                                    <th class="border-b-2 whitespace-nowrap">TAREA</th>
                                    <th class="border-b-2 whitespace-nowrap">TIPO / JUICIO</th>
                                    <th class="border-b-2 whitespace-nowrap">ASIGNADO</th>
                                    <th class="border-b-2 whitespace-nowrap">LÍMITE</th>
                                    <th class="border-b-2 whitespace-nowrap">ESTADO</th>
                                    <th class="border-b-2 whitespace-nowrap text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tareas as $t)
                                    <tr class="{{ $loop->index % 2 > 0 ? 'bg-gray-200' : '' }}">
                                        <td>
                                            <h6 class="mb-1 font-medium">{{ $t->titulo }}</h6>
                                            <small class="text-gray-500">{{ \Illuminate\Support\Str::limit($t->descripcion ?? '', 60) }}</small>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 font-medium">{{ $t->tipo === 'juicio' ? 'Juicio ' . ($t->juicio->cod_satje ?? '#'.$t->juicio_id) : 'Externa' }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 font-medium">{{ $t->asignado->name ?? '—' }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 font-medium {{ $t->esta_vencida ? 'text-red-600 font-bold' : '' }}">
                                                {{ $t->fecha_limite ? $t->fecha_limite->format('d/m/Y') : '—' }}
                                            </h6>
                                            @if($t->esta_vencida)<span class="badge bg-danger text-white">Vencida</span>@endif
                                        </td>
                                        <td>
                                            @if($t->estado === 'pendiente')
                                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-yellow-100 text-yellow-800">Pendiente</span>
                                            @elseif($t->estado === 'en_proceso')
                                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-blue-100 text-blue-800">En proceso</span>
                                            @elseif($t->estado === 'completada')
                                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-green-100 text-green-800">Completada</span>
                                            @elseif($t->estado === 'convertida')
                                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-purple-100 text-purple-800">Juicio (act. #{{ $t->actividad_id }})</span>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-gray-200 text-gray-700">Cancelada</span>
                                            @endif
                                            @php $prevCont = trim(strip_tags($t->contenido ?? '')); @endphp
                                            @if($prevCont !== '')
                                                <div class="mt-1 text-xs text-gray-600" title="{{ $prevCont }}">
                                                    {{ \Illuminate\Support\Str::limit($prevCont, 80) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="flex justify-center gap-1 flex-wrap">
                                                @php
                                                    $esAsignado = (int) $t->asignado_a === (int) auth()->id();
                                                    $tAvance = trim(strip_tags($t->contenido ?? '')) !== ''
                                                        || ($t->adjuntos_count ?? 0) > 0
                                                        || !in_array($t->estado, ['pendiente']);
                                                @endphp
                                                @if($esAsignado)
                                                <button class="btn btn-warning text-white border-0"
                                                    wire:click.prevent="Edit({{ $t->id }})" title="Ver / Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if(in_array($t->estado, ['pendiente']))
                                                <button class="btn btn-primary text-white border-0"
                                                    wire:click.prevent="cambiarEstado({{ $t->id }}, 'en_proceso')" title="Iniciar">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                @endif
                                                @if(in_array($t->estado, ['pendiente', 'en_proceso']))
                                                <button class="btn btn-success text-white border-0"
                                                    wire:click.prevent="cambiarEstado({{ $t->id }}, 'completada')" title="Marcar completada">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                                @if($t->estado === 'completada' && $t->tipo === 'juicio' && !$t->actividad_id)
                                                <button class="btn btn-info text-white border-0"
                                                    wire:click.prevent="abrirConvertir({{ $t->id }})" title="Convertir en actividad">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                                @endif
                                                @endif
                                                @if(!$t->actividad_id && (!$tAvance || $esAsignado))
                                                <button class="btn btn-danger text-white border-0"
                                                    wire:click.prevent="destroy({{ $t->id }})" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200">
                                        <td colspan="6"><h6 class="text-center">NO HAY TAREAS</h6></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-spam-12 p-5">
                {{ $tareas->links() }}
            </div>

            </div>
        </div>
    @else

        @include('livewire.tareas.form')

    @endif

</div>
