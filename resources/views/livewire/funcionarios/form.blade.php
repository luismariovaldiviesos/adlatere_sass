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
