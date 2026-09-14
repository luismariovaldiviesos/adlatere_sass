<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">
        <div class="col-span-12 md:col-span-8 relative">
            <label class="form-label text-base font-bold">Buscar Abogado (Usuario)</label>
            <div class="relative">
                <input type="text" 
                       wire:model.debounce.500ms="searchAbogado" 
                       class="form-control h-12 text-lg" 
                       placeholder="Escriba el nombre o correo del abogado...">
                
                @if(strlen($searchAbogado) > 0 && $showAbogadoDropdown)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 overflow-hidden" style="max-height: 250px; overflow-y: auto;">
                        @forelse($abogados_list as $abg)
                            <div wire:key="abg-{{ $abg->id }}" 
                                 wire:click="selectAbogado({{ $abg->id }}, '{{ addslashes($abg->name) }}')" 
                                 class="p-3 hover:bg-blue-100 cursor-pointer border-b last:border-0 transition-colors duration-200">
                                <span class="font-bold block text-lg">{{ $abg->name }}</span>
                                <small class="text-gray-500 block">{{ $abg->email }} | {{ $abg->especialidad ?? 'Sin especialidad' }}</small>
                            </div>
                        @empty
                            <div class="p-4 text-center bg-gray-50">
                                <p class="text-gray-600">No se encontró el abogado.</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="col-span-12 md:col-span-4">
            <button class="btn btn-primary text-lg px-8 py-2.5 w-full h-12 flex justify-center items-center" wire:click="addAbogado">
                <i class="fas fa-plus mr-2"></i> Asignar Patrocinador
            </button>
        </div>
    </div>

    <div class="overflow-x-auto mt-6 shadow-sm rounded-lg">
        <table class="table text-base border">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-4 font-bold border-b-2">Nombre Completo</th>
                    <th class="p-4 font-bold border-b-2">Correo / Especialidad</th>
                    <th class="p-4 font-bold border-b-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $abogadosAsignados = $selected_id > 0 ? \App\Models\Juicio::find($selected_id)->abogados : [];
                @endphp

                @forelse($abogadosAsignados as $abg)
                <tr wire:key="abogado-asignado-{{ $abg->id }}" class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="p-4 border-b font-medium">{{ $abg->name }}</td>
                    <td class="p-4 border-b">
                        <small class="block">{{ $abg->email }}</small>
                        <small class="text-gray-500 block">{{ $abg->especialidades->pluck('nombre')->implode(', ') ?? 'N/A' }}</small>
                    </td>
                    <td class="p-4 text-center border-b">
                        <button class="btn btn-outline-danger btn-sm" wire:click="removeAbogado({{ $abg->id }})" title="Remover del Juicio">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-10 text-center text-gray-500 text-lg">
                        <i class="fas fa-user-tie fa-3x mb-3 text-gray-300 block"></i>
                        Aún no hay abogados asignados a este juicio.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>