{{-- <div class="p-8 grid grid-cols-12 gap-6">
   
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
</div> --}}

<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">
        
        {{-- BUSCADOR DINÁMICO DE CUSTOMERS Y BOTÓN NUEVO --}}
        <div class="col-span-12 md:col-span-6 relative">
            <label class="form-label text-base font-bold">Buscar Sujeto (Nombre o Cédula/RUC)</label>
            <div class="relative">
                <!-- Buscador principal -->
                <input type="text" 
                       wire:model.debounce.500ms="searchCustomer" 
                       class="form-control h-12 text-lg" 
                       placeholder="Escriba para buscar...">
                
                <!-- Resultados del Buscador Flotantes -->
                @if(strlen($searchCustomer) > 0 && $this->showDropdown)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 overflow-hidden" style="max-height: 250px; overflow-y: auto;">
                        
                        @forelse($this->customers as $c)
                            <!-- Si encuentra a alguien, muestra: -->
                            <div wire:click="selectCustomer({{ $c->id }}, '{{ $c->businame }}')" 
                                 class="p-3 hover:bg-blue-100 cursor-pointer border-b last:border-0 transition-colors duration-200">
                                <span class="font-bold block text-lg">{{ $c->businame }}</span>
                                <small class="text-gray-500 block">{{ $c->valueidenti }}</small>
                            </div>
                        @empty
                            <!-- EL BOTÓN MÁGICO SI NO ENCUENTRA A NADIE -->
                            <div class="p-4 text-center bg-gray-50">
                                <p class="text-gray-600 mb-2">No se encontró el sujeto en la base de datos.</p>
                                <button type="button" wire:click="showCreateCustomer" class="btn btn-primary btn-sm w-full h-10 shadow-md">
                                    <i class="fas fa-plus mr-2"></i> Crear como Nuevo Sujeto
                                </button>
                            </div>
                        @endforelse

                    </div>
                @endif
            </div>
            @error('cliente_id') <span class="text-theme-6 mt-1 block">Debe seleccionar un cliente de la lista</span> @enderror
        </div>

        {{-- SELECT DE ROL (Arreglado a minúsculas por tu ENUM) --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Rol en el Juicio</label>
            <select wire:model.defer="rol" class="form-select h-12 text-lg">
                <option value="">Seleccione…</option>
                <option value="actor">ACTOR</option>
                <option value="demandado">DEMANDADO</option>
            </select>
            @error('rol') <span class="text-theme-6 mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- BOTON AGREGAR AL JUICIO --}}
        <div class="col-span-12 md:col-span-2">
            <button type="button" class="btn btn-primary w-full h-12 shadow-md" wire:click="addParticipante">
                <i class="fas fa-plus mr-2"></i> Agregar
            </button>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS EN VIVO --}}
    <div class="overflow-x-auto mt-6 shadow-sm rounded-lg">
        <table class="table text-base border">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-4 font-bold border-b-2">Identificación</th>
                    <th class="p-4 font-bold border-b-2">Nombre Completo / Razón Social</th>
                    <th class="p-4 font-bold border-b-2 text-center">Rol</th>
                    <th class="p-4 font-bold border-b-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $participantes = $selected_id > 0 ? \App\Models\Juicio::find($selected_id)->participantes : [];
                @endphp

                @forelse($participantes as $p)
                <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="p-4 border-b">{{ $p->valueidenti }}</td>
                    <td class="p-4 uppercase border-b font-medium">{{ $p->businame }}</td>
                    <td class="p-4 text-center border-b">
                        <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm 
                            {{ strtolower($p->pivot->rol) == 'actor' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ strtoupper($p->pivot->rol) }}
                        </span>
                    </td>
                    <td class="p-4 text-center border-b">
                        <button class="btn btn-outline-danger btn-sm" wire:click="removeParticipante({{ $p->id }})" title="Remover del Juicio">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-10 text-center text-gray-500 text-lg">
                        <i class="fas fa-users fa-3x mb-3 text-gray-300 block"></i>
                        Aún no hay sujetos procesales asignados a este juicio.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>



