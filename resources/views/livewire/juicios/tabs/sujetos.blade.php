<div class="p-8 grid grid-cols-12 gap-6">
    {{-- Campos: datos camiseta, número, talla, estatura, peso, posición principal, otra posición, lateralidad --}}
    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Datos camiseta</label>
        <input type="text"  wire:model.defer="datos_camiseta" class="form-control h-12 text-lg" placeholder="Nombre impreso / detalles">
         @error('datos_camiseta')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">Número camiseta</label>
        <input type="number" wire:model.defer="numero_camiseta" class="form-control h-12 text-lg" placeholder="10">
         @error('numero_camiseta')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">Talla camiseta</label>
        <input type="text" wire:model.defer="talla_camiseta" class="form-control h-12 text-lg" placeholder="S / M / L / XL">
          @error('talla_camiseta')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Posición principal</label>
        <input type="text" wire:model.defer="posicion_principal" class="form-control h-12 text-lg" placeholder="Delantero / Defensa / Volante / Arquero">
         @error('posicion_principal')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Otra posición</label>
        <input type="text" wire:model.defer="otra_posicion" class="form-control h-12 text-lg" placeholder="Posición secundaria">
             @error('otra_posicion')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-base">Lateralidad</label>
        <select wire:model.defer ="lateralidad" class="form-select h-12 text-lg">
            <option value="">Seleccione…</option>
            <option>Diestro</option>
            <option>Zurdo</option>
            <option>Ambidiestro</option>
        </select>
         @error('lateralidad')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>
    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-base">Instituto anterior</label>
        <input type="text" wire:model.defer="academia_anterior" step="0.01" class="form-control h-12 text-lg" placeholder="academia prueba">
        @error('academia_anterior')
                        <x-alert msg="{{ $message  }}" />
        @enderror
    </div>

    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-base">Años Jugando</label>
        <input type="text" wire:model.defer= "años_practica" step="0.1" class="form-control h-12 text-lg" placeholder="2 años">
    @error('años_practica')
                        <x-alert msg="{{ $message  }}" />
        @enderror
    </div>

    <div class="col-span-12 flex justify-end">
        <button class="btn btn-primary text-lg px-8 py-2.5" wire:click="saveFicha">
            Guardar
        </button>
    </div>
</div>
