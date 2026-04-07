<div class="p-8 grid grid-cols-12 gap-6">
    {{-- nombre, tipo documento, ci-ruc, direccion, email, telefono, notas --}}
    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Nombre del representante</label>
        <input type="text"  wire:model.defer="businame" class="form-control h-12 text-lg" placeholder="Nombre completo">
          @error('businame')
                    <x-alert msg="{{ $message  }}" />
         @enderror
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">Tipo documento</label>
        <select wire:model.defer="typeidenti" class="form-select h-12 text-lg">
            <option value="">Seleccione…</option>
            <option>CI</option>
            <option>RUC</option>
            <option>Pasaporte</option>
        </select>
        @error('typeidenti')
                <x-alert msg="{{ $message  }}" />   
        @enderror
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">CI / RUC</label>
        <input type="text"  wire:model.lazy="valueidenti" class="form-control h-12 text-lg" placeholder="Número de documento">
           @error('valueidenti')
        <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12">
        <label class="form-label text-base">Dirección</label>
        <input type="text"  wire:model.defer="address"   class="form-control h-12 text-lg" placeholder="Calle, número, sector">
        @error('address')
                <x-alert msg="{{ $message  }}" />
        @enderror
    </div>

    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Email</label>
        <input type="email"  wire:model.defer="email"  class="form-control h-12 text-lg" placeholder="correo@ejemplo.com">
        @error('email')
                <x-alert msg="{{ $message  }}" />   
        @enderror
    </div>

    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Teléfono</label>
        <input type="text"  wire:model.defer="phone"  class="form-control h-12 text-lg" placeholder="09xxxxxxxx">
        @error('phone')
                <x-alert msg="{{ $message  }}" /> 
        @enderror
    </div>

    <div class="col-span-12">
        <label class="form-label text-base">Notas</label>
        <textarea  wire:model.defer="notes" class="form-control text-lg" rows="3" placeholder="Observaciones"></textarea>
      @error('notes')
        <x-alert msg="{{ $message }}" />
    @enderror
    </div>

    <div class="col-span-12 flex justify-end">
        <button class="btn btn-primary text-lg px-8 py-2.5" wire:click="saveRepresentante">
            Guardar
        </button>
    </div>
</div>
