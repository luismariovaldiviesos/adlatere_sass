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
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >TIPO DE ACTIVIDAD</th>
                                     {{-- <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >DESCRIPCIÓN</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap" >FASE</th>                                    --}}
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center" >ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($plantillas as $plantilla) 
                                    <tr class=" dark:bg-dark-1 {{ $loop->index % 2> 0 ? 'bg-gray-200' : '' }}">

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $plantilla->id }}</h6>

                                        </td>

                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $plantilla->nombre }}</h6>
                                        </td>
                                        <td class="dark:border-dark-5">
                                            <h6 class="mb-1 font-medium">{{ $plantilla->tipoActividad->nombre }}</h6>
                                        </td>

                                       

                                        <td class="dark:border-dark-5 text-center">
                                            <div class="d-flex justify-content-center">
                                                {{-- @if ($procedimiento->asuntos->count() < 1) --}}
                                                    <button class="btn btn-danger text-white border-0"
                                                    onclick="destroy('plantillas-tipos-actividad','Destroy', {{ $plantilla->id }})"
                                                    type="button">
                                                        <i class=" fas fa-trash f-2x"></i>
                                                    </button>
                                                {{-- @endif --}}
                                                <button class="btn btn-primary text-white border-0"
                                                    wire:click.prevent="Preview({{ $plantilla->id }})"
                                                    title="Previsualizar"
                                                    type="button">
                                                        <i class=" fas fa-eye f-2x"></i>
                                                </button>
                                                <button class="btn btn-warning text-white border-0 ml-3"
                                                    wire:click.prevent="Edit({{ $plantilla->id }})"
                                                    title="Editar"
                                                    type="button">
                                                        <i class=" fas fa-edit f-2x"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200 dark:bg-dark-1">
                                        <td colspan="2">
                                            <h6 class="text-center">    NO HAY TIPOS DE ACTIVIDADES REGISTRADOS </h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-spam-12 p-5">
                {{ $plantillas->links() }}
            </div>


            </div>
        </div>
    @else

        @include('livewire.plantillas_tipos_actividades.form')

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

        window.addEventListener('show-preview-modal', event => {
            var modal = document.getElementById('modalPreviewTemplate');
            modal.classList.add("overflow-y-auto", "show");
            modal.style.cssText = "margin-top: 0px; margin-left: -100px; z-index: 10000; display: block; background: rgba(0,0,0,0.5);";
        });

        function closePreviewModal() {
            var modal = document.getElementById('modalPreviewTemplate');
            modal.classList.remove("overflow-y-auto", "show");
            modal.style.cssText = "display: none;";
        }
    </script>

    {{-- MODAL DE PREVISUALIZACION --}}
    <div wire:ignore.self id="modalPreviewTemplate" class="modal" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-xl" style="max-width: 1000px; margin-top: 2rem;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-lg mr-auto">
                        <b class="text-theme-1">{{ $preview_title }}</b>
                    </h2>
                    <button type="button" onclick="closePreviewModal()" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body bg-gray-100" style="max-height: 75vh; overflow-y: auto;">
                    <div style="background: white; padding: 2.5cm; margin: 0 auto; width: 100%; max-width: 21cm; min-height: 29.7cm; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <div class="ql-snow">
                            <div class="ql-editor" style="padding:0;">
                                {!! $preview_content !!}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer text-right">
                    <button type="button" onclick="closePreviewModal()" class="btn btn-secondary">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>

</div>
