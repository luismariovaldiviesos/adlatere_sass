<div>

    @if($puedeEntrar)

    @if (!$form)

        <div class="intro-y col-span-12">

            <div class="intro-y box">

            <h2 class="text-lg font-medium text-center text-them-1 py-4">
                {{ $componentName }}
            </h2>

            {{-- AQUI LLAMAMOS AL COMPONENTE SEARH --}}
                <x-search />
            {{-- AQUI LLAMAMOS AL COMPONENTE SEARH --}}
            @if(auth()->user()->profile === 'Admin')
            <div class="px-5 flex items-center gap-2">
                <input type="checkbox" wire:model="verArchivados" id="verArch" class="form-check-input">
                <label for="verArch" class="text-xs">Ver archivados</label>
            </div>
            @endif

            <div class="p-5">
                <div class="preview">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="text-theme-1">
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >SATJE</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >MATERIA</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >PROCEDIMIENTO</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >ASUNTO</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >ESTADO PROCESAL</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >CLIENTE</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >ABOGADO</th>                                    
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center" >ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($juicios   as $juicio )
                                    <tr class=" dark:bg-dark-1 {{ $loop->index % 2> 0 ? 'bg-gray-200' : '' }}">

                                         {{-- <td>
                                            <img src="{{ $alumno->img }}" data-action="zoom" alt="img-category" width="100">
                                        </td> --}}

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->cod_satje }}</h6>
                                        </td>
                                         <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->asunto->procedimiento->materia->nombre ?? '—' }}</h6>
                                        </td>
                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->asunto->procedimiento->nombre ?? '—' }}</h6>   
                                        </td>
                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->asunto->nombre ?? '—' }}</h6>
                                        </td>
                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->estadoProcesal->nombre ?? '—' }}</h6>
                                        </td>
                                      <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->clientes->pluck('businame')->implode(', ') ?: '—' }}</h6>
                                        </td>
                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $juicio->abogados->pluck('name')->implode(', ') ?: '—' }}</h6>
                                        </td>


                                        <td class="dark:border-dark-5 text-center">
                                            <div class="d-flex justify-content-center">
                                                @if($verArchivados)
                                                <button class="btn btn-success btn-sm" type="button" onclick="restaurar('juicios', {{ $juicio->id }})" title="Restaurar juicio archivado">
                                                    <i class="fas fa-undo"></i> Restaurar
                                                </button>
                                                @else
                                                @can('eliminar_juicio')
                                                    @if(!$juicio->tiene_contenido)
                                                    <button class="btn btn-danger text-white border-0"
                                                    onclick="destroy('juicios','Destroy', {{ $juicio->id }})"
                                                    type="button" title="Eliminar definitivo (sin movimientos)">
                                                        <i class=" fas fa-trash f-2x"></i>
                                                    </button>
                                                    @else
                                                    <button class="btn btn-warning text-white border-0"
                                                    onclick="destroy('juicios','Destroy', {{ $juicio->id }})"
                                                    type="button" title="Archivar (conserva datos)">
                                                        <i class="fas fa-archive f-2x"></i>
                                                    </button>
                                                    @endif
                                                @endcan
                                                @can('editar_juicio')
                                                <button class="btn btn-warning text-white border-0 ml-3"
                                                    wire:click.prevent="Edit({{ $juicio->id }})"
                                                    type="button">
                                                        <i class=" fas fa-edit f-2x"></i>
                                                </button>
                                                @endcan

                                                @php
                                                    $sem = $juicio->semaforo;
                                                    $semTxt = $sem['color'] === 'yellow' ? '#000' : '#fff';
                                                @endphp
                                                <span title="{{ $sem['texto'] }}" style="display:inline-flex;align-items:center;gap:5px;background-color:{{ $sem['hex'] }};color:{{ $semTxt }};font-weight:700;font-size:11px;padding:3px 10px;border-radius:9999px;margin-right:6px;vertical-align:middle;box-shadow:0 0 8px {{ $sem['hex'] }};white-space:nowrap;">
                                                    <span style="display:inline-block;width:8px;height:8px;border-radius:9999px;background-color:{{ $semTxt }};"></span>
                                                    {{ $sem['dias'] }}d · {{ strtoupper($sem['color']) }}
                                                </span>
                                                <button type="button" wire:click="openRoadmap({{ $juicio->id }})" class="btn btn-sm btn-outline-primary" title="Ver histórico">
                                                    <i class="fas fa-history"></i> Histórico
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200 dark:bg-dark-1">
                                        <td colspan="2">
                                            <h6 class="text-center">    NO HAY ALUMNOS  </h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-spam-12 p-5">
                {{ $juicios->links() }}
            </div>


            </div>
        </div>
    @else

        @include('livewire.juicios.form')

    @endif

     {{-- CONTENEDOR PERMANENTE PARA EL MODAL --}}
        {{-- CONTENEDOR PERMANENTE PARA EL MODAL --}}
    <div>
        @if($showFirmarModal)
            <div wire:click.self="cancelarFirma" class="fixed top-0 left-0 w-full h-full flex items-center justify-center" style="z-index: 110; background-color: rgba(0,0,0,0.6);">
                <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl overflow-y-auto border-2 border-slate-300" style="width: 90%; max-width: 800px; max-height: 90vh;">

                    <div class="flex justify-between items-center px-6 py-3 mb-4 sticky top-0 z-10" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0.7rem 0.7rem 0 0;">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-signature mr-2"></i> Firmar actividad digitalmente
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cancelarFirma" wire:loading.attr="disabled" wire:target="cancelarFirma" class="btn btn-sm px-4 font-bold" style="background-color: #60a5fa; color: #000; border: 1px solid #1e3a8a;">
                                <span wire:loading.remove wire:target="cancelarFirma">Cerrar</span>
                                <span wire:loading wire:target="cancelarFirma">Cerrando...</span>
                            </button>
                            <button type="button" wire:click="cancelarFirma" wire:loading.attr="disabled" wire:target="cancelarFirma" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
                        </div>
                    </div>

                    <div class="px-6 pb-5">
                        <div class="mb-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <h3 class="font-bold text-slate-800">Escoja el certificado con el cual va a firmar la actividad</h3>
                            </div>

                            @if(($firmaOpciones['despacho']['permitido'] ?? false) && !($firmaOpciones['despacho']['disponible'] ?? false))
                            <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800">Certificado del despacho no disponible</div>
                                        <p class="text-sm text-slate-700 mt-1">{{ $firmaOpciones['despacho']['motivo'] ?? 'El certificado del despacho no está configurado.' }}</p>
                                        <p class="text-xs text-slate-500 mt-1">Debe configurarlo en Configuración &gt; Empresa &gt; Firma del despacho.</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($firmaModo === 'automatico')
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-800">Certificado institucional obligatorio</div>
                                            <p class="text-xs text-slate-600 mt-1">La política del despacho exige firmar con este certificado.</p>
                                            <p class="text-sm text-slate-700 mt-1"><strong>Titular:</strong> {{ $firmaOpciones['despacho']['titular'] ?? '—' }}</p>
                                            @if($firmaOpciones['despacho']['vencimiento'] ?? null)
                                                <p class="text-xs text-slate-500">Vence: {{ $firmaOpciones['despacho']['vencimiento'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @if($firmaOpciones['despacho']['disponible'] ?? false)
                                        <button type="button" wire:click="$set('firmaOrigen', 'despacho')"
                                            aria-pressed="{{ $firmaOrigen === 'despacho' ? 'true' : 'false' }}"
                                            class="relative w-full text-left rounded-lg border-2 p-3 cursor-pointer transition {{ $firmaOrigen === 'despacho' ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div class="min-w-0 pr-7">
                                                    <div class="font-semibold text-slate-800">Certificado del despacho</div>
                                                    <div class="text-xs text-slate-600 mt-1 truncate">{{ $firmaOpciones['despacho']['titular'] ?? '—' }}</div>
                                                    @if($firmaOpciones['despacho']['vencimiento'] ?? null)
                                                        <div class="text-xs text-slate-500 mt-1">Vence: {{ $firmaOpciones['despacho']['vencimiento'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($firmaOrigen === 'despacho')
                                                <span class="absolute top-2.5 right-2.5 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-sm"><i class="fas fa-check text-xs"></i></span>
                                            @endif
                                        </button>
                                    @endif

                                    @if($firmaOpciones['personal']['permitido'] ?? false)
                                        <button type="button" wire:click="$set('firmaOrigen', 'personal')"
                                            aria-pressed="{{ $firmaOrigen === 'personal' ? 'true' : 'false' }}"
                                            class="relative w-full text-left rounded-lg border-2 p-3 cursor-pointer transition {{ $firmaOrigen === 'personal' ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-id-card"></i>
                                                </div>
                                                <div class="min-w-0 pr-7">
                                                    <div class="font-semibold text-slate-800">Mi certificado personal</div>
                                                    @if($firmaOpciones['personal']['disponible'] ?? false)
                                                        <div class="text-xs text-slate-600 mt-1 truncate">{{ $firmaOpciones['personal']['titular'] ?? '—' }}</div>
                                                        @if($firmaOpciones['personal']['vencimiento'] ?? null)
                                                            <div class="text-xs text-slate-500 mt-1">Vence: {{ $firmaOpciones['personal']['vencimiento'] }}</div>
                                                        @endif
                                                    @else
                                                        <div class="text-xs text-slate-600 mt-1">Buscar certificado personal o de terceros .p12/.pfx</div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($firmaOrigen === 'personal')
                                                <span class="absolute top-2.5 right-2.5 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-sm"><i class="fas fa-check text-xs"></i></span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                                @error('firmaOrigen') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        @if($firmaModo !== 'automatico' && $firmaOrigen === 'personal' && !($firmaOpciones['personal']['disponible'] ?? false))
                            <div class="mb-4 rounded-lg border border-slate-200 p-3">
                                <label class="font-semibold text-slate-800 block mb-2">Buscar certificado personal</label>
                                <input type="file" wire:model="firmaArchivoPersonal" accept=".p12,.pfx" class="form-control">
                                <div wire:loading wire:target="firmaArchivoPersonal" class="text-primary text-sm mt-2">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Cargando archivo...
                                </div>
                                @if($firmaArchivoPersonal)
                                    <p class="text-xs text-emerald-700 mt-2"><i class="fas fa-check-circle mr-1"></i>Archivo seleccionado: {{ $firmaArchivoPersonal->getClientOriginalName() }}</p>
                                @else
                                    <p class="text-xs text-slate-500 mt-2">Selecciona el archivo .p12/.pfx que usarás para esta firma.</p>
                                @endif
                                @error('firmaArchivoPersonal') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="font-bold text-slate-800 block mb-2">
                                <i class="fas fa-key text-amber-500 mr-1"></i>
                                Contraseña del certificado {{ $firmaOrigen === 'despacho' ? 'del despacho' : 'personal' }}
                            </label>
                            <input type="password" wire:model="firmaPasswordConfirm" class="form-control w-full h-10" placeholder="Ingresa la contraseña del PKCS#12" autocomplete="new-password">
                            @error('firmaPasswordConfirm') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-2 sticky bottom-0" style="border-radius: 0 0 0.7rem 0.7rem;">
                        <button type="button" wire:click="cancelarFirma" class="btn btn-outline-secondary px-4 py-2">Cancelar</button>
                        <button type="button" wire:click="confirmarFirma" wire:loading.attr="disabled" wire:target="confirmarFirma" class="btn btn-primary px-5 py-2">
                            <span wire:loading.remove wire:target="confirmarFirma"><i class="fas fa-signature mr-2"></i>Firmar y guardar</span>
                            <span wire:loading wire:target="confirmarFirma"><i class="fas fa-spinner fa-spin mr-2"></i>Firmando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($showRoadmapModal && $juicioRoadmap)
            <!-- Fondo oscuro (clic fuera de la caja también cierra; sin blur para no relentizar con muchos items) -->
            <div id="roadmap-overlay" wire:click="closeRoadmap" onclick="if(event.target===this){cerrarRoadmapModal();}" class="fixed top-0 left-0 w-full h-full flex items-center justify-center" style="z-index: 100; background-color: rgba(0,0,0,0.6);">
                <!-- Caja del modal centrada (borde de ventana estilo modal) -->
                <div onclick="event.stopPropagation()" class="bg-white rounded-xl relative shadow-2xl overflow-y-auto border-2 border-slate-300" style="width: 90%; max-width: 800px; max-height: 90vh;">

                    <!-- Cabecera del Modal (fija: el botón Cerrar siempre visible aunque haya muchos items) -->
                    <div class="flex justify-between items-center px-6 py-3 mb-4 sticky top-0 z-10" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0.7rem 0.7rem 0 0;">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-history mr-2"></i> Histórico del Juicio: {{ $juicioRoadmap->cod_satje }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="descargarHistorialPdf" wire:loading.attr="disabled" wire:target="descargarHistorialPdf" class="btn btn-sm btn-danger text-white font-bold" title="Descargar todo el historial en PDF">
                                <span wire:loading.remove wire:target="descargarHistorialPdf"><i class="fas fa-file-pdf mr-1"></i> PDF</span>
                                <span wire:loading wire:target="descargarHistorialPdf">Generando...</span>
                            </button>
                            <button type="button" wire:click="closeRoadmap" wire:loading.attr="disabled" wire:target="closeRoadmap" onclick="cerrarRoadmapModal()" class="btn btn-sm px-4 font-bold" style="background-color: #60a5fa; color: #000; border: 1px solid #1e3a8a;">
                                <span wire:loading.remove wire:target="closeRoadmap">Cerrar</span>
                                <span wire:loading wire:target="closeRoadmap">Cerrando…</span>
                            </button>
                            <button type="button" wire:click="closeRoadmap" wire:loading.attr="disabled" wire:target="closeRoadmap" onclick="cerrarRoadmapModal()" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
                        </div>
                    </div>
                    <div class="px-6 pb-6">

                    <!-- Resumen Estado Actual -->
                    <div class="p-4 rounded-md mb-6 shadow-sm border" style="background-color: #f0f9ff; border-color: #e0f2fe;">
                        <p class="font-bold" style="color: #075985;">Estado Procesal Actual:</p>
                        <p class="text-lg uppercase font-semibold" style="color: #0c4a6e;">
                            {{ $juicioRoadmap->estadoProcesal->nombre ?? 'Sin Estado Procesal' }}
                        </p>
                    </div>

                    <!-- TIMELINE GRÁFICO (El Histórico) -->
                    <div class="relative ml-4 mt-8" style="border-left: 2px solid #1e3a8a;">
                        @forelse($historialRoadmap as $hito)
                            <div class="mb-8 relative group" style="margin-left: 2rem;">
                                <!-- Punto del Timeline -->
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-white rounded-full transition-transform duration-300 group-hover:scale-125" style="border: 4px solid #1e3a8a; left: -45px; top: 4px;">
                                </span>
                                
                                <!-- Tarjeta del Hito -->
                                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300">
                                    <div class="flex justify-between items-start mb-2">
                                        <!-- Tipo de Movimiento -->
                                        <h3 class="font-bold text-gray-800 uppercase text-sm">
                                            <i class="fas fa-check-circle mr-1" style="color: #1e3a8a;"></i>
                                            {{ str_replace('_', ' ', $hito->tipo_movimiento) }}
                                        </h3>
                                        <!-- Fecha -->
                                        <span class="text-xs font-bold text-gray-600 bg-gray-200 px-3 py-1 rounded-full">
                                            {{ \Carbon\Carbon::parse($hito->created_at)->format('d/m/Y - H:i') }}
                                        </span>
                                    </div>
                                    
                                    <!-- Descripción del suceso -->
                                    <p class="text-gray-600 text-sm mt-1">{{ $hito->descripcion }}</p>
                                    
                                    <!-- Usuario Responsable -->
                                    @if($hito->user)
                                        <div class="text-xs text-gray-500 mt-3 pt-2 border-t border-gray-200 flex items-center">
                                            <i class="fas fa-user-circle mr-2 text-gray-400"></i> Registrado por: <span class="font-semibold ml-1">{{ $hito->user->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="ml-8 text-gray-500 font-medium italic">
                                <i class="fas fa-info-circle mr-2"></i> No hay historial registrado para este juicio aún.
                            </div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        function cerrarRoadmapModal() {
            var o = document.getElementById('roadmap-overlay');
            if (o) o.style.display = 'none';
        }
    </script>

    {{-- para el buscador  --}}
    <script>
        //... tu script intacto ...
        document.addEventListener('click', (e) => {
            if(e.target.id == 'search'){
                KioskBoard.run('#search', {})

                // para no hacer click fuera click dentro
                document.getElementById('search').blur()
                document.getElementById('search').focus()

                const inputSearch = document.getElementById('search')
                inputSearch.addEventListener('change', (e) => {
                 @this.search = e.target.value
                 })

            }
        })
    </script>
</div>

    @else
    <div class="alert alert-danger" role="alert">
        <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
    </div>
    @endif

</div>



