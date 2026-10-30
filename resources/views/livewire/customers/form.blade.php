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
                            <label  class="form-label">Nombre-Razón Social</label>
                            <input wire:model='businame' id="businame" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('businame')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Tipo</label>
                            <select wire:model='typeidenti' id="typeidenti" class="form-control form-control-lg border-start-0 kioskboard">
                                <option selected>Elegir</option>
                                <option value="ruc">RUC</option>
                                <option value="ci">CI</option>
                                <option value="06">PASAPORTE</option>
                              </select>
                            @error('businame')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">CI-RUC</label>
                            <input wire:model='valueidenti' id="valueidenti" type="text" data-kioskboard-type="numpad" class="form-control form-control-lg border-start-0 kioskboard" maxlength="13">
                            @error('valueidenti')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Dirección</label>
                            <input wire:model='address' id="address" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('address')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                    </div>

                </div>

                <div class="mt-3">
                    <div class="sm:grid grid-cols-3 gap-5">
                        <div>
                            <label class="form-label">Estado civil</label>
                            <select wire:model="estado_civil" class="form-control form-control-lg">
                                <option value="">Seleccione...</option>
                                <option value="Soltero/a">Soltero/a</option>
                                <option value="Casado/a">Casado/a</option>
                                <option value="Divorciado/a">Divorciado/a</option>
                                <option value="Viudo/a">Viudo/a</option>
                                <option value="Unión de hecho">Unión de hecho</option>
                                <option value="Otro">Otro</option>
                            </select>
                            @error('estado_civil')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Profesión / Ocupación</label>
                            <input wire:model="profesion_ocupacion" type="text" class="form-control form-control-lg" maxlength="150" placeholder="Ej: Abogado, Comerciante, Estudiante">
                            @error('profesion_ocupacion')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Cantón</label>
                            <select wire:model="canton_id" class="form-control form-control-lg">
                                <option value="">Seleccione...</option>
                                @foreach($cantones as $canton)
                                    <option value="{{ $canton->id }}">{{ $canton->nombre }}</option>
                                @endforeach
                            </select>
                            @error('canton_id')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="sm:grid grid-cols-3 gap-5">
                        <div>
                            <label  class="form-label">Email</label>
                            <input wire:model='email' id="email" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('email')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Teléfono</label>
                            <input wire:model='phone' id="phone" type="text" data-kioskboard-type="numpad" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('phone')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Notas</label>
                            <input wire:model='notes' id="notes" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('notes')
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

       // KioskBoard.run('.kioskboard', {})

       document.querySelectorAll(".kioskboard").forEach(i => i.addEventListener("change", e =>{

            switch(e.currentTarget.id)
            {
                case 'businame':
                    @this.businame = e.target.value
                    break
                case 'valueidenti':
                    @this.valueidenti = e.target.value
                    break
                case 'address':
                    @this.address = e.target.value
                    break
                case 'email':
                    @this.email = e.target.value
                    break
                case 'phone':
                    @this.phone = e.target.value
                    brea
                case 'notes':
                    @this.notes = e.target.value
                    break
            }

       }))

    </script>

</div>




