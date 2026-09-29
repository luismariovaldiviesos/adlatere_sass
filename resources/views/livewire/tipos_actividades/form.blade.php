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
                            <label  class="form-label">Nombre Actividad</label>
                            <input wire:model='nombre' id="nombre" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('nombre')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                      <div div class="sm:grid grid-cols-2 gap-5">
                            <label class="form-label flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model='es_firmable' class="form-check-input mt-1" value="1">
                                <span class="form-label mb-0">¿Es firmable electrónicamente?</span>
                            </label>
                            @error('es_firmable')
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
