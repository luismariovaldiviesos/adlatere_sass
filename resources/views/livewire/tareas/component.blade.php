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
                    @if(auth()->user()->profile === 'Abogado')
                    <div class="flex items-center gap-2 pb-2">
                        <input type="checkbox" wire:model="soloMias" id="soloMiasTareas" class="form-check-input">
                        <label for="soloMiasTareas" class="text-xs">Solo mis asignadas</label>
                    </div>
                    @endif
                </div>

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
                                                @if($t->esta_vencida)<span class="badge" style="background-color:#dc3545; color:#ffffff;">Vencida</span>@endif
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
                                                    @endphp
                                                    <!-- Ver detalles de la tarea (todos los perfiles) -->
                                                    <button class="btn text-white border-0"
                                                        style="background-color:#475569;"
                                                        wire:click.prevent="verDetalles({{ $t->id }})" title="Ver detalles de la tarea">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <!-- Ver detalles de actividad si existe -->
                                                    @if($t->actividad_id)
                                                    <button class="btn text-white border-0"
                                                        style="background-color:#0891b2;"
                                                        wire:click.prevent="verDetallesActividad({{ $t->actividad_id }})" title="Ver detalles de actividad">
                                                        <i class="fas fa-list"></i>
                                                    </button>
                                                    @endif
                                                    <!-- Cambiar fecha de vencimiento (Admin/Asistente) -->
                                                    @if($puedeEditarFecha && in_array($t->estado, ['pendiente', 'en_proceso'], true))
                                                    <button class="btn text-white border-0"
                                                        style="background-color:#7c3aed;"
                                                        wire:click.prevent="abrirFechaVencimiento({{ $t->id }})" title="Cambiar fecha de vencimiento">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </button>
                                                    @endif
                                                    @if($esAsignado)
                                                    @if($t->estado === 'en_proceso' && !$t->esta_vencida)
                                                    <button class="btn btn-warning text-white border-0"
                                                        wire:click.prevent="Edit({{ $t->id }})" title="Ver / Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @endif
                                                    @if($t->estado === 'pendiente')
                                                    <button class="btn btn-primary text-white border-0"
                                                        wire:click.prevent="cambiarEstado({{ $t->id }}, 'en_proceso')" title="Iniciar">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                    @endif
                                                    @if($t->estado === 'en_proceso' && $t->esta_vencida)
                                                    <button class="btn btn-secondary text-white border-0" disabled
                                                        title="Tarea vencida: no se puede editar ni completar. Solicite ampliar la fecha límite.">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                    @elseif($t->estado === 'en_proceso' && (trim(strip_tags($t->contenido ?? '')) !== '' || ($t->adjuntos_count ?? 0) > 0))
                                                    <button class="btn btn-success text-white border-0"
                                                        wire:click.prevent="cambiarEstado({{ $t->id }}, 'completada')" title="Marcar completada">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    @endif
                                                    @if($t->estado === 'completada' && $t->tipo === 'juicio' && !$t->actividad_id)
                                                    <button class="btn btn-info text-white border-0 fw-bold"
                                                        wire:click.prevent="abrirConvertir({{ $t->id }})" title="Convertir en actividad" style="background:#0dcaf0; border-color:#0dcaf0;">
                                                        <i class="fas fa-gavel"></i> Convertir
                                                    </button>
                                                    @endif
                                                    @endif
                                                    @if($puedeEliminar && !$t->actividad_id)
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

    {{-- MODAL CONVERTIR TAREA EN ACTIVIDAD --}}
    @if($convertir_id)
    <div id="convertir-tarea-overlay" wire:click="cancelarConvertir"
         class="fixed top-0 left-0 w-full h-full flex items-center justify-center"
         style="z-index: 100; background-color: rgba(0,0,0,0.6);">
        <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl border-2 border-slate-300"
             style="width: 90%; max-width: 520px;">
            <div class="flex justify-between items-center px-6 py-3" style="background: linear-gradient(135deg, #065f46 0%, #10b981 100%); border-radius: 0.7rem 0.7rem 0 0;">
                <h2 class="text-lg font-bold text-white">
                    <i class="fas fa-gavel mr-2"></i> Convertir tarea en actividad
                </h2>
                <button type="button" wire:click="cancelarConvertir" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-1">Tarea #{{ $convertir_id }}</p>
                <p class="font-medium text-gray-800 mb-4">{{ $conv_tarea_titulo }}</p>

                <label class="form-label text-xs">Tipo de actividad</label>
                <select wire:model.defer="conv_tipo_actividad_id" class="form-select w-full">
                    <option value="">Seleccione...</option>
                    @foreach($tiposActividades as $tipoActividad)
                    <option value="{{ $tipoActividad->id }}">{{ $tipoActividad->nombre }}</option>
                    @endforeach
                </select>
                @error('conv_tipo_actividad_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror

                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="cancelarConvertir" class="btn btn-outline-secondary">Cancelar</button>
                    <button type="button" wire:click="confirmarConvertir" wire:loading.attr="disabled" wire:target="confirmarConvertir" class="btn btn-success text-white">
                        <span wire:loading.remove wire:target="confirmarConvertir"><i class="fas fa-check mr-1"></i> Confirmar</span>
                        <span wire:loading wire:target="confirmarConvertir">Convirtiendo...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL VER DETALLES TAREA --}}
    <div>
        @if($this->verDetalle_id && $this->tareaDetalle)
            <!-- Fondo oscuro (clic fuera de la caja también cierra) -->
            <div id="tarea-detalle-overlay" wire:click="cerrarDetalles" onclick="if(event.target===this){cerrarTareaDetalleModal();}" class="fixed top-0 left-0 w-full h-full flex items-center justify-center" style="z-index: 100; background-color: rgba(0,0,0,0.6);">
                <!-- Caja del modal centrada -->
                <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl overflow-y-auto border-2 border-slate-300" style="width: 90%; max-width: 800px; max-height: 90vh;">

                    <!-- Cabecera del Modal (fija: el botón Cerrar siempre visible) -->
                    <div class="flex justify-between items-center px-6 py-3 mb-4 sticky top-0 z-10" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0.7rem 0.7rem 0 0;">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-eye mr-2"></i> Detalles de la tarea #{{ $this->tareaDetalle->id }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cerrarDetalles" wire:loading.attr="disabled" wire:target="cerrarDetalles" onclick="cerrarTareaDetalleModal()" class="btn btn-sm px-4 font-bold" style="background-color: #60a5fa; color: #000; border: 1px solid #1e3a8a;">
                                <span wire:loading.remove wire:target="cerrarDetalles">Cerrar</span>
                                <span wire:loading wire:target="cerrarDetalles">Cerrando…</span>
                            </button>
                            <button type="button" wire:click="cerrarDetalles" wire:loading.attr="disabled" wire:target="cerrarDetalles" onclick="cerrarTareaDetalleModal()" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
                        </div>
                    </div>
                    <div class="px-6 pb-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Título</label>
                                <p class="font-medium">{{ $this->tareaDetalle->titulo }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Estado</label>
                                <p>
                                    @php
                                        $estadosTarea = ['pendiente'=>'Pendiente','en_proceso'=>'En proceso','completada'=>'Completada','convertida'=>'Convertida','cancelada'=>'Cancelada'];
                                        $badgesTarea = ['pendiente'=>'bg-yellow-100 text-yellow-800','en_proceso'=>'bg-blue-100 text-blue-800','completada'=>'bg-green-100 text-green-800','convertida'=>'bg-purple-100 text-purple-800','cancelada'=>'bg-gray-200 text-gray-700'];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm {{ $badgesTarea[$this->tareaDetalle->estado] ?? 'bg-gray-200 text-gray-700' }}">
                                        {{ $estadosTarea[$this->tareaDetalle->estado] ?? $this->tareaDetalle->estado }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Asignado a</label>
                                <p>{{ $this->tareaDetalle->asignado->name ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Fecha límite</label>
                                <p>{{ $this->tareaDetalle->fecha_limite ? $this->tareaDetalle->fecha_limite->format('d/m/Y') : '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tipo</label>
                                <p>{{ $this->tareaDetalle->tipo === 'juicio' ? 'Vinculada a juicio' : 'Externa' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Juicio</label>
                                <p>{{ $this->tareaDetalle->juicio->cod_satje ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Descripción</label>
                            <p class="text-gray-700">{{ $this->tareaDetalle->descripcion ?: '—' }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Redacción / Contenido</label>
                            <div class="bg-gray-50 border rounded p-4 min-h-[120px] text-gray-700 whitespace-pre-wrap">
                                {{ $this->tareaDetalle->contenido ?: '—' }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Adjuntos ({{ $this->tareaDetalle->adjuntos_count ?? 0 }})</label>
                            @forelse($this->tareaDetalle->adjuntos as $adj)
                            @php $adjUrl = route('tenant.media', ['path' => $adj->ruta_archivo]); @endphp
                            <div class="flex items-center gap-3 p-3 bg-gray-50 border rounded mb-2">
                                <i class="fas fa-paperclip text-gray-400 text-lg"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $adj->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $adj->tipo_archivo }} · {{ number_format($adj->tamano_archivo / 1024, 1) }} KB · Subido por {{ $adj->usuario->name ?? '—' }} el {{ $adj->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <a href="{{ $adjUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">Ver</a>
                                <a href="{{ $adjUrl }}" download="{{ e($adj->nombre) }}" class="btn btn-outline-secondary btn-sm ml-1">Descargar</a>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 italic">Sin adjuntos.</p>
                            @endforelse
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Historial de acciones</label>
                            <div class="relative ml-4 mt-8" style="border-left: 2px solid #1e3a8a;">
                                @forelse($this->tareaDetalle->historiales->sortByDesc('created_at') as $hist)
                                <div class="mb-8 relative group" style="margin-left: 2rem;">
                                    <span class="absolute flex items-center justify-center w-6 h-6 bg-white rounded-full transition-transform duration-300 group-hover:scale-125" style="border: 4px solid #1e3a8a; left: -45px; top: 4px;"></span>
                                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-bold text-gray-800 uppercase text-sm">
                                                <i class="{{ $hist->icon }} mr-1" style="color: #1e3a8a;"></i>
                                                {{ $hist->label }}
                                            </h3>
                                            <span class="text-xs font-bold text-gray-600 bg-gray-200 px-3 py-1 rounded-full">
                                                {{ $hist->created_at->format('d/m/Y - H:i') }}
                                            </span>
                                        </div>
                                        <p class="text-gray-600 text-sm mt-1">{{ $hist->descripcion }}</p>
                                        @if($hist->usuario)
                                        <div class="text-xs text-gray-500 mt-3 pt-2 border-t border-gray-200 flex items-center">
                                            <i class="fas fa-user-circle mr-2 text-gray-400"></i> Registrado por: <span class="font-semibold ml-1">{{ $hist->usuario->name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="ml-8 text-gray-500 font-medium italic">
                                    <i class="fas fa-info-circle mr-2"></i> Sin historial de acciones.
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL VER DETALLES ACTIVIDAD --}}
    <div>
        @if($this->verActividad_id && $this->actividadDetalle)
            <!-- Fondo oscuro (clic fuera de la caja también cierra) -->
            <div id="actividad-detalle-overlay" wire:click="cerrarDetallesActividad" onclick="if(event.target===this){cerrarActividadDetalleModal();}" class="fixed top-0 left-0 w-full h-full flex items-center justify-center" style="z-index: 100; background-color: rgba(0,0,0,0.6);">
                <!-- Caja del modal centrada -->
                <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl overflow-y-auto border-2 border-slate-300" style="width: 90%; max-width: 800px; max-height: 90vh;">

                    <!-- Cabecera del Modal (fija: el botón Cerrar siempre visible) -->
                    <div class="flex justify-between items-center px-6 py-3 mb-4 sticky top-0 z-10" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0.7rem 0.7rem 0 0;">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-history mr-2"></i> Detalles de la actividad #{{ $this->actividadDetalle->id }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cerrarDetallesActividad" wire:loading.attr="disabled" wire:target="cerrarDetallesActividad" onclick="cerrarActividadDetalleModal()" class="btn btn-sm px-4 font-bold" style="background-color: #60a5fa; color: #000; border: 1px solid #1e3a8a;">
                                <span wire:loading.remove wire:target="cerrarDetallesActividad">Cerrar</span>
                                <span wire:loading wire:target="cerrarDetallesActividad">Cerrando…</span>
                            </button>
                            <button type="button" wire:click="cerrarDetallesActividad" wire:loading.attr="disabled" wire:target="cerrarDetallesActividad" onclick="cerrarActividadDetalleModal()" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
                        </div>
                    </div>
                    <div class="px-6 pb-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tipo de actividad</label>
                                <p class="font-medium">{{ $this->actividadDetalle->tipoActividad->nombre ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Estado</label>
                                <p>
                                    @php
                                        $estados = ['pendiente'=>'Pendiente','en_proceso'=>'En proceso','completada'=>'Completada','convertida'=>'Convertida','cancelada'=>'Cancelada'];
                                        $badgeColors = ['pendiente'=>'bg-yellow-100 text-yellow-800','en_proceso'=>'bg-blue-100 text-blue-800','completada'=>'bg-green-100 text-green-800','convertida'=>'bg-purple-100 text-purple-800','cancelada'=>'bg-gray-200 text-gray-700'];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm {{ $badgeColors[$this->actividadDetalle->estado ?? 'pendiente'] ?? 'bg-gray-200 text-gray-700' }}">
                                        {{ $estados[$this->actividadDetalle->estado ?? 'pendiente'] ?? '—' }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Responsable</label>
                                <p>{{ $this->actividadDetalle->user->name ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Fecha</label>
                                <p>{{ $this->actividadDetalle->fecha_actividad ? \Carbon\Carbon::parse($this->actividadDetalle->fecha_actividad)->format('d/m/Y H:i') : '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Origen</label>
                                <p>{{ $this->actividadDetalle->origen ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Juicio</label>
                                <p>{{ $this->actividadDetalle->juicio->cod_satje ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Descripción</label>
                            <p class="text-gray-700">{{ $this->actividadDetalle->descripcion ?: '—' }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Contenido</label>
                            <div class="bg-gray-50 border rounded p-4 min-h-[120px] text-gray-700 whitespace-pre-wrap">
                                {{ $this->actividadDetalle->contenido ?: '—' }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">Adjuntos ({{ $this->actividadDetalle->adjuntos_count ?? 0 }})</label>
                            @forelse($this->actividadDetalle->adjuntos as $adj)
                            @php $adjUrl = route('tenant.media', ['path' => $adj->ruta_archivo]); @endphp
                            <div class="flex items-center gap-3 p-3 bg-gray-50 border rounded mb-2">
                                <i class="fas fa-paperclip text-gray-400 text-lg"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $adj->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $adj->tipo_archivo }} · {{ number_format($adj->tamano_archivo / 1024, 1) }} KB · Subido por {{ $adj->usuario->name ?? '—' }} el {{ $adj->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <a href="{{ $adjUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">Ver</a>
                                <a href="{{ $adjUrl }}" download="{{ e($adj->nombre) }}" class="btn btn-outline-secondary btn-sm ml-1">Descargar</a>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 italic">Sin adjuntos.</p>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL CAMBIAR FECHA DE VENCIMIENTO --}}
    <div>
        @if($abrirFecha)
            <div id="fecha-vencimiento-overlay" wire:click="cerrarFechaVencimiento" onclick="if(event.target===this){cerrarFechaVencimientoModal();}" class="fixed top-0 left-0 w-full h-full flex items-center justify-center" style="z-index: 100; background-color: rgba(0,0,0,0.6);">
                <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl overflow-y-auto border-2 border-slate-300" style="width: 90%; max-width: 480px;">
                    <div class="flex justify-between items-center px-6 py-3 mb-4 sticky top-0 z-10" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0.7rem 0.7rem 0 0;">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-calendar-alt mr-2"></i> Fecha de vencimiento
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cerrarFechaVencimiento" wire:loading.attr="disabled" wire:target="cerrarFechaVencimiento" onclick="cerrarFechaVencimientoModal()" class="btn btn-sm px-4 font-bold" style="background-color: #60a5fa; color: #000; border: 1px solid #1e3a8a;">
                                <span wire:loading.remove wire:target="cerrarFechaVencimiento">Cerrar</span>
                                <span wire:loading wire:target="cerrarFechaVencimiento">Cerrando…</span>
                            </button>
                            <button type="button" wire:click="cerrarFechaVencimiento" wire:loading.attr="disabled" wire:target="cerrarFechaVencimiento" onclick="cerrarFechaVencimientoModal()" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <p class="text-sm text-gray-600 mb-3">Tarea #{{ $fecha_id }}. Puede dejar la fecha vacía para quitar el vencimiento.</p>
                        <label class="form-label">Nueva fecha límite</label>
                        <input type="date" wire:model.defer="fecha_edit" class="form-control" min="{{ now()->toDateString() }}">
                        @error('fecha_edit') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        <div class="text-right mt-5">
                            <button type="button" wire:click="cerrarFechaVencimiento" class="btn btn-outline-secondary mr-1">Cancelar</button>
                            <button type="button" wire:click="guardarFechaVencimiento" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function cerrarTareaDetalleModal() {
            var o = document.getElementById('tarea-detalle-overlay');
            if (o) o.style.display = 'none';
        }
        function cerrarActividadDetalleModal() {
            var o = document.getElementById('actividad-detalle-overlay');
            if (o) o.style.display = 'none';
        }
        function cerrarFechaVencimientoModal() {
            var o = document.getElementById('fecha-vencimiento-overlay');
            if (o) o.style.display = 'none';
        }

        function loadTareaQuillEditor() {
            if (typeof Quill !== 'undefined') { startTareaWordEditor(); return; }
            if (!document.querySelector('#quill-css')) {
                let link = document.createElement('link');
                link.id = 'quill-css'; link.rel = 'stylesheet';
                link.href = 'https://cdn.quilljs.com/1.3.6/quill.snow.css';
                document.head.appendChild(link);
            }
            let script = document.querySelector('#quill-script');
            if (!script) {
                script = document.createElement('script');
                script.id = 'quill-script';
                script.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
                document.head.appendChild(script);
            }
            script.addEventListener('load', function () { startTareaWordEditor(); });
        }

        function startTareaWordEditor() {
            const editorDom = document.querySelector('#editor-tarea-hoja');
            if (!editorDom) return;
            if (window.tareaEditorInstance) {
                try {
                    if (!window.tareaEditorInstance.root.isConnected) { window.tareaEditorInstance = null; }
                } catch (e) { window.tareaEditorInstance = null; }
            }
            if (!editorDom.classList.contains('ql-container')) {
                var quill = new Quill('#editor-tarea-hoja', {
                    theme: 'snow',
                    placeholder: 'Redacte aquí el trabajo de la tarea...',
                    modules: { toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['clean']
                    ]}
                });
                const toolbarContainer = document.querySelector('#toolbar-tarea-container');
                const quillToolbar = document.querySelector('#toolbar-tarea-container ~ .editable-container .ql-toolbar, .editable-container .ql-toolbar');
                if (toolbarContainer && quillToolbar) {
                    toolbarContainer.appendChild(quillToolbar);
                }
                window.tareaEditorInstance = quill;
                quill.on('text-change', function () { @this.set('contenido', quill.root.innerHTML); });
            }
            if (window.tareaPendingContent !== undefined && window.tareaPendingContent !== null && window.tareaEditorInstance) {
                try {
                    window.tareaEditorInstance.root.innerHTML = window.tareaPendingContent;
                    window.tareaPendingContent = null;
                } catch (e) {}
            }
        }

        function iniciarEditorTarea() {
            var el = document.getElementById('editor-tarea-hoja');
            if (!el) return;
            if (el.classList.contains('ql-container')) return;
            loadTareaQuillEditor();
        }

        document.addEventListener('livewire:load', function () {
            setTimeout(iniciarEditorTarea, 250);
            if (window.Livewire && Livewire.hook) {
                Livewire.hook('message.processed', function () {
                    setTimeout(iniciarEditorTarea, 200);
                });
            }
        });
    </script>

</div>
