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
                            <label  class="form-label">Prioridad</label>
                            <input wire:model='prioridad' id="prioridad" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="20" @if($selected_id > 0) disabled @endif>
                            @error('prioridad')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Mensaje del aviso (opcional)</label>
                            <input wire:model='mensaje' id="mensaje" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="255" placeholder="Ej: Escalar a coordinación">
                            @error('mensaje')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Días en verde (≤)</label>
                            <input wire:model='dias_verde' id="dias_verde" type="number" min="0" class="form-control form-control-lg border-start-0 kioskboard">
                            @error('dias_verde')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Días en amarillo (≤)</label>
                            <input wire:model='dias_amarillo' id="dias_amarillo" type="number" min="0" class="form-control form-control-lg border-start-0 kioskboard">
                            @error('dias_amarillo')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">Días en rojo (>)</label>
                            <input wire:model='dias_rojo' id="dias_rojo" type="number" min="0" class="form-control form-control-lg border-start-0 kioskboard">
                            @error('dias_rojo')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div class="flex items-end gap-2 pb-1">
                            <input type="checkbox" wire:model='activo' id="activo" class="form-check-input">
                            <label for="activo" class="form-label mb-0">Activo</label>
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

</div>
