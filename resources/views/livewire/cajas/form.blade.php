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
                            <label  class="form-label">Nombre Caja</label>
                            <input wire:model='nombre' id="nombre" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('nombre')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div class="grid grid-cols-6">
                            <div class="col-span-6 bg-amber-500 p-2 rounded">
                                <label class="form-label">Usuario Caja</label>
                                <select wire:model='user_id' class="form-select form-select-lg sm:mr-2">
                                   <option value="">Elegir</option>
                                   @foreach ($usuarios as $user )
                                   <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach

                                </select>
                                @error('user_id')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                            </div>
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
       document.querySelectorAll(".kioskboard").forEach(i => i.addEventListener("change", e =>{

            switch(e.currentTarget.id)
            {
                case 'nombre':
                    @this.nombre = e.target.value
                    break
            }

       }))

    </script>

</div>




