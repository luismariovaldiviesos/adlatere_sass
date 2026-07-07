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
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >ID</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >NOMBRE</th>
                                     <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >DESCRIPCIÓN</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >FASE</th>                                   
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >JUICIOS EN FASE</th>                                   
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center" >ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($estados as $estado) 
                                    <tr class=" dark:bg-dark-1 {{ $loop->index % 2> 0 ? 'bg-gray-200' : '' }}">

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $estado->id }}</h6>

                                        </td>

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $estado->nombre }}</h6>
                                            {{-- <small class="font-normal">{{ $MATERI->unidades->count() }} unidades en este edificio</small> --}}
                                        </td>
                                         <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $estado->descripcion }}</h6>
                                        </td>

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $estado->fase->nombre }}</h6>
                                        </td>

                                        <td class="dark:border-dark-5">
                                            {{-- @if($estado->juicios_count > 0)
                                            <span class="px-3 py-1.5 rounded-full text-sm font-bold shadow-sm inline-flex items-center gap-2
                                                {{ $estado->juicios_count >= 10 ? 'bg-danger/20 text-danger border border-danger/30' : 'bg-warning/20 text-warning border border-warning/30' }}">
                                                <span class="w-2 h-2 rounded-full animate-pulse {{ $estado->juicios_count >= 10 ? 'bg-danger' : 'bg-warning' }}"></span>
                                                {{ $estado->juicios_count }} Juicios Activos
                                            </span>
                                        @else
                                            <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-slate-100 text-slate-500 border border-slate-200 inline-flex items-center">
                                                Sin procesos
                                            </span>
                                        @endif --}}
                                        @if($estado->juicios_count > 0  )
                                        <div class="alert alert-warning-soft show flex items-center mb-2 px-4 py-3 rounded-lg border border-warning/20" role="alert">
                                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-3 text-warning"></i>
                                            <div class="text-base font-semibold text-warning-900">
                                                 Hay <span class="font-extrabold text-lg">{{ $estado->juicios_count }}</span> expedientes en esta fase.
                                            </div>
                                        </div>
                                        @endif
                                        </td>

                                       

                                        <td class="dark:border-dark-5 text-center">
                                            <div class="d-flex justify-content-center">
                                                {{-- @if ($procedimiento->asuntos->count() < 1) --}}
                                                    <button class="btn btn-danger text-white border-0"
                                                    onclick="destroy('estados-procesales','Destroy', {{ $estado->id }})"
                                                    type="button">
                                                        <i class=" fas fa-trash f-2x"></i>
                                                    </button>
                                                {{-- @endif --}}
                                                <button class="btn btn-warning text-white border-0 ml-3"
                                                    wire:click.prevent="Edit({{ $estado->id }})"
                                                    type="button">
                                                        <i class=" fas fa-edit f-2x"></i>
                                                    </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200 dark:bg-dark-1">
                                        <td colspan="2">
                                            <h6 class="text-center">    NO HAY ESTADOS REGISTRADOS </h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-spam-12 p-5">
                {{ $estados->links() }}
            </div>


            </div>
        </div>
    @else

        @include('livewire.estados_procesales.form')

    @endif

    {{-- @include('livewire.sales.keyboard') --}}


    {{-- para el buscador  --}}
    <script>
         document.addEventListener('click', (e) => {
            if(e.target.id == 'search'){
                KioskBoard.run('#search', {})

                // para no hacer click fuera click dentro
                document.getElementById('search').blur()
                document.getElementById('search').focus()

                const inputSearch = document.getElementById('search')
                inputSearch.addEventListener('change', (e) => {
                 @this.search = e.target.value  // iguala lo que esta en id search con search del comoponente
                 })

            }
        })


    </script>

</div>
