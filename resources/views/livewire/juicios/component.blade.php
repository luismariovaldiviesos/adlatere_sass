<div>

    @if (!$form)

        <div class="intro-y col-span-12">

            <div class="intro-y box">

            <h2 class="text-lg font-medium text-center text-them-1 py-4">
                {{ $componentName }}
            </h2>

            {{-- AQUI LLAMAMOS AL COMPONENTE SEARH --}}
                <x-search />
            {{-- AQUI LLAMAMOS AL COMPONENTE SEARH --}}

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
                                    
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >FECHA INICIO</th>
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
                                            <h6 class="mb-1 font-medium">{{ $juicio->fecha_inicio ?? '—' }}</h6>
                                        </td>


                                        <td class="dark:border-dark-5 text-center">
                                            <div class="d-flex justify-content-center">
                                                {{-- @if ($customer->orders->count() < 1) --}}
                                                    <button class="btn btn-danger text-white border-0"
                                                    onclick="destroy('juicios','Destroy', {{ $juicio->id }})"
                                                    type="button">
                                                        <i class=" fas fa-trash f-2x"></i>
                                                    </button>
                                                {{-- @endif --}}
                                                <button class="btn btn-warning text-white border-0 ml-3"
                                                    wire:click.prevent="Edit({{ $juicio->id }})"
                                                    type="button">
                                                        <i class=" fas fa-edit f-2x"></i>
                                                </button>

                                                <button type="button" wire:click="openRoadmap({{ $juicio->id }})" class="btn btn-sm btn-outline-primary" title="Ver Roadmap">
                                                    <i class="fas fa-route"></i> Roadmap
                                                </button>
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

    {{-- @include('livewire.sales.keyboard') --}}
     {{-- CONTENEDOR PERMANENTE PARA EL MODAL --}}
        {{-- CONTENEDOR PERMANENTE PARA EL MODAL --}}
    <div>
        @if($showRoadmapModal && $juicioRoadmap)
            <!-- Fondo oscuro -->
            <div class="fixed top-0 left-0 w-full h-full z-50 flex items-center justify-center" style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
                
                <!-- Caja del modal centrada -->
                <div class="bg-white rounded-lg p-6 relative shadow-2xl overflow-y-auto" style="width: 90%; max-width: 800px; max-height: 90vh;">
                    
                    <!-- Cabecera del Modal -->
                    <div class="flex justify-between items-center border-b pb-3 mb-4 sticky top-0 bg-white z-10">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-map-signs mr-2" style="color: #1e3a8a;"></i> Roadmap del Juicio: {{ $juicioRoadmap->cod_satje }}
                        </h2>
                        <button wire:click="closeRoadmap" class="text-gray-500 hover:text-red-500 text-3xl font-bold leading-none">&times;</button>
                    </div>

                    <!-- Resumen Estado Actual -->
                    <div class="p-4 rounded-md mb-6 shadow-sm border" style="background-color: #f0f9ff; border-color: #e0f2fe;">
                        <p class="font-bold" style="color: #075985;">Estado Procesal Actual:</p>
                        <p class="text-lg uppercase font-semibold" style="color: #0c4a6e;">
                            {{ $juicioRoadmap->estadoProcesal->nombre ?? 'Sin Estado Procesal' }}
                        </p>
                    </div>

                    <!-- TIMELINE GRÁFICO (El Roadmap) -->
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
                    
                    <!-- Pie del Modal -->
                    <div class="mt-8 text-right border-t pt-4">
                        <button wire:click="closeRoadmap" class="btn btn-secondary px-8 py-2">Cerrar Roadmap</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

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



</div>



