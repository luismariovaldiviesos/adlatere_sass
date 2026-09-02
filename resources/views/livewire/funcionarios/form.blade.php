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
                            <label  class="form-label">Nombre</label>
                            <input wire:model='nombre' id="nombre" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('nombre')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div class="grid grid-cols-6">
                            <div class="col-end-2 bg-amber-500">
                                <label class="form-label">Cargo</label>
                                <select wire:model='cargo' class="form-select form-select-lg sm:mr-2">
                                   <option selected="elegir">Elegir</option>
                                  
                                   <option value="Juez">Juez </option>
                                   <option value="Secretario">Secretario </option>
                                   <option value="Ayudante">Ayudante </option>
                                   <option value="Citador">Citador </option>
                                   <option value="Otro">Otro </option>                                   

                                </select>
                                @error('cargo')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                            </div>
                        </div>
                         <div>
                            <label  class="form-label">Teléfono</label>
                            <input wire:model='telefono' id="telefono" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('telefono')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>
                            <div>
                                <label  class="form-label">Email</label>
                                <input wire:model='email' id="email" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                                @error('email')
                                    <x-alert msg="{{ $message }}" />
                                @enderror  
                            </div>
                          <div class="col-span-12 mt-4">
    <label class="form-label font-bold text-base">Unidades Judiciales Asignadas</label>
    
    {{-- Contenedor con altura máxima y scroll vertical --}}
    <div class="border border-gray-200 rounded-md bg-white shadow-sm p-4 overflow-y-auto" style="max-height: 320px;">
        
        @forelse($unidades_agrupadas as $canton => $unidades)
            <div class="mb-5 last:mb-0">
                {{-- Encabezado del Cantón / Ciudad --}}
                <h3 class="font-bold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-md mb-3 text-sm uppercase sticky top-0 z-10 shadow-sm">
                    <i class="fas fa-map-marker-alt mr-2 text-primary"></i> {{ $canton }}
                </h3>
                
                {{-- Cuadrícula de checkboxes a 2 columnas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-2">
                    @foreach($unidades as $unidad)
                        <div class="flex items-start">
                            <input type="checkbox" 
                                   id="unidad_{{ $unidad->id }}" 
                                   value="{{ $unidad->id }}" 
                                   wire:model.defer="unidades_seleccionadas"
                                   class="form-check-input w-5 h-5 mt-0.5 border-gray-300 rounded focus:ring-primary mr-2 cursor-pointer transition-all">
                            <label for="unidad_{{ $unidad->id }}" class="cursor-pointer text-sm text-gray-600 hover:text-gray-900 leading-tight">
                                {{ $unidad->nombre }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-4">
                No hay unidades judiciales registradas.
            </div>
        @endforelse
        
    </div>
    @error('unidades_seleccionadas') <span class="text-theme-6 mt-1 text-sm">{{ $message }}</span> @enderror
</div>
                            


                    </div>
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
