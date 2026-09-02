<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">
        
        {{-- BUSCADOR DINÁMICO DE FUNCIONARIOS --}}
        <div class="col-span-12 md:col-span-6 relative">
            <label class="form-label text-base font-bold">Buscar Funcionario Judicial</label>
            <div class="relative">
                <input type="text" 
                       wire:model.debounce.500ms="searchFuncionario" 
                       class="form-control h-12 text-lg" {{ !$unidad_id ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                       placeholder="{{ $unidad_id ? 'Escriba el nombre del Juez, Secretario...' : 'Seleccione una Unidad Judicial primero' }}"
                @if(!$unidad_id) disabled @endif>
                   @if(!$unidad_id)
        <small class="text-theme-12 block mt-1"><i class="fas fa-exclamation-triangle"></i> Debe seleccionar y guardar la Unidad Judicial en la pestaña principal para buscar funcionarios.</small>
    @endif
                
                @if(strlen($searchFuncionario) > 0 && $showFuncionarioDropdown)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 overflow-hidden" style="max-height: 250px; overflow-y: auto;">
                        @forelse($funcionarios_list as $func)
                            <div wire:click="selectFuncionario({{ $func->id }}, '{{ $func->nombre }}')" 
                                 class="p-3 hover:bg-blue-100 cursor-pointer border-b last:border-0 transition-colors duration-200">
                                <span class="font-bold block text-lg">{{ $func->nombre }}</span>
                                <small class="text-gray-500 block">{{ $func->cargo }} | {{ $func->email ?? 'Sin correo' }}</small>
                            </div>
                        @empty
                            <div class="p-4 text-center bg-gray-50">
                                <p class="text-gray-600">No se encontró el funcionario en la base de datos.</p>
                                <a href="{{ route('funcionarios') }}" target="_blank" class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="fas fa-plus mr-2"></i> Registrar en catálogo de Funcionarios
                                </a>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
            @error('funcionario_id') <span class="text-theme-6 mt-1 block">Debe seleccionar un funcionario de la lista</span> @enderror
        </div>

        {{-- SELECCIONAR O ESCRIBIR EL ROL EN EL JUICIO --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Rol en el Juicio</label>
            <select wire:model.defer="rol_en_juicio" class="form-select h-12 text-lg">
                <option value="">Seleccione…</option>
                <option value="Juez Ponente">Juez Ponente</option>
                <option value="Secretario">Secretario</option>
                <option value="Fiscal de Turno">Fiscal de Turno</option>
                <option value="Ayudante Judicial">Ayudante Judicial</option>
                <option value="Citador">Citador</option>
            </select>
            @error('rol_en_juicio') <span class="text-theme-6 mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- BOTÓN AGREGAR --}}
        <div class="col-span-12 md:col-span-2">
            <button class="btn btn-primary text-lg px-8 py-2.5 w-full h-12 flex justify-center items-center" wire:click="addFuncionario">
                <i class="fas fa-plus mr-2"></i> Asignar
            </button>
        </div>
    </div>

    {{-- LISTADO DE ASIGNADOS --}}
    <div class="overflow-x-auto mt-6 shadow-sm rounded-lg">
        <table class="table text-base border">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-4 font-bold border-b-2">Nombre</th>
                    <th class="p-4 font-bold border-b-2">Cargo Base</th>
                    <th class="p-4 font-bold border-b-2 text-center">Rol en este Juicio</th>
                    <th class="p-4 font-bold border-b-2 text-center">Contacto</th>
                    <th class="p-4 font-bold border-b-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $funcionariosAsignados = $selected_id > 0 ? \App\Models\Juicio::find($selected_id)->funcionarios : [];
                @endphp

                @forelse($funcionariosAsignados as $fa)
                <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="p-4 border-b uppercase font-medium">{{ $fa->nombre }}</td>
                    <td class="p-4 border-b">{{ $fa->cargo }}</td>
                    <td class="p-4 text-center border-b">
                        <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm bg-blue-100 text-blue-800">
                            {{ $fa->pivot->rol_en_juicio }}
                        </span>
                    </td>
                    <td class="p-4 text-center border-b">
                        <small class="text-gray-500 block">{{ $fa->email }}</small>
                        <small class="text-gray-500 block">{{ $fa->telefono }}</small>
                    </td>
                    <td class="p-4 text-center border-b">
                        <button class="btn btn-outline-danger btn-sm" wire:click="removeFuncionario({{ $fa->id }})" title="Remover del Juicio">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-10 text-center text-gray-500 text-lg">
                        <i class="fas fa-gavel fa-3x mb-3 text-gray-300 block"></i>
                        Aún no hay funcionarios judiciales asignados a este juicio.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>