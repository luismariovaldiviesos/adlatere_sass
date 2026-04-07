<div class="p-8 grid grid-cols-12 gap-6">


    {{-- CAMPOS: ci, nombres, fecha nacimiento, colegio, genero --}}
    <div class="col-span-12 md:col-span-8 grid grid-cols-12 gap-6">
        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">CI</label>
            <input type="text" wire:model='ci' class="form-control h-12 text-lg"  placeholder="CI del alumno">
             @error('ci')
                    <x-alert msg="{{ $message  }}" />
             @enderror
        </div>

        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">Nombres</label>
            <input type="text" wire:model='nombres' class="form-control h-12 text-lg" placeholder="Nombres completos">
          @error('nombres')
                    <x-alert msg="{{ $message  }}" />
             @enderror
        </div>

        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">Fecha de nacimiento</label>
            <input type="date" wire:model='fecha_nacimiento' class="form-control h-12 text-lg">
            @error('fecha_nacimiento')
                    <x-alert msg="{{ $message  }}" />
             @enderror
        </div>

        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">Colegio</label>
            <input type="text" wire:model='colegio' class="form-control h-12 text-lg" placeholder="Colegio">
            @error('colegio')
                    <x-alert msg="{{ $message  }}" />
             @enderror
        </div>

        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base" >Género</label>
            <select wire:model='genero' class="form-select h-12 text-lg">
                <option value="">Seleccione…</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
                <option value="X">Otro / Prefiere no decir</option>
            </select>
            @error('genero')
                    <x-alert msg="{{ $message  }}" />
             @enderror
        </div>

           <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">Foto del alumno</label>
            <input type="file" wire:model='foto' class="form-control h-12 text-base">
            @error('foto')
                    <x-alert msg="{{ $message  }}" />
             @enderror
         </div>
        <div class="col-span-12 flex justify-end">
            <button class="btn btn-primary text-lg px-8 py-2.5" wire:click="savePerfil">
                Guardar
            </button>
        </div>
    </div>
</div>
