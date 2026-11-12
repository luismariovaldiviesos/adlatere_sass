<div class="intro-y col-span-12">
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">
                {{ $componentName  }} | <span class="font-normal">{{ $action }}</span>
            </h2>
        </div>

        <div class="p-5 ">
            <div class="preview">

                <div class="mt-3">
                    <div class="sm:grid grid-cols-2 gap-5">
                        <div>
                            <label  class="form-label">Nombre Unidad</label>
                            <input wire:model.defer='nombre' id="nombre" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('nombre')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div class="grid grid-cols-6">
                            <div class="col-end-2 bg-amber-500">
                                <label class="form-label">Cantón</label>
                                <select wire:model.defer='canton_id' class="form-select form-select-lg sm:mr-2">
                                   <option selected="elegir">Elegir</option>
                                   @foreach ($cantones as $canton )
                                   <option value="{{ $canton->id }}">{{ $canton->nombre }}</option>
                                    @endforeach

                                </select>
                                @error('canton_id')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                            </div>
                        </div>


                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label font-bold">Materias que tramita esta unidad</label>
                    <div class="border border-gray-200 rounded-md bg-white shadow-sm p-4 overflow-y-auto" style="max-height: 280px;">
                        @forelse($materias as $materia)
                            <label class="flex items-center gap-2 text-sm py-1.5 cursor-pointer">
                                <input type="checkbox"
                                       wire:model.defer="materias_seleccionadas"
                                       value="{{ $materia->id }}"
                                       class="form-check-input border-gray-300 rounded focus:ring-primary">
                                <span>{{ $materia->nombre }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-2">No hay materias registradas.</p>
                        @endforelse
                    </div>
                    <small class="text-xs text-gray-500 block mt-1">
                        Solo estas materias estarán disponibles al seleccionar esta unidad en la carátula.
                    </small>
                    @error('materias_seleccionadas') <span class="text-theme-6 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mt-5">
                    <x-back />

                    <x-save />
                </div>

            </div>
        </div>

    </div>


    <script>
        // KioskBoard.run('#categoryName', {})
        // const inputCatName = document.getElementById('categoryName')
        // if(inputCatName){
        //     inputCatName.addEventListener('change', ()=> {
        //         @this.name = e.target.value
        //     })
        // }
    </script>

</div>
