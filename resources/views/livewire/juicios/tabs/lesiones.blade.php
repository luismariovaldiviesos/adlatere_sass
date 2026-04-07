<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">
        {{-- fecha lesión (mes y año) --}}
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Fecha de lesión</label>
            <input wire:model.defer= 'fecha' type="month" class="form-control h-12 text-lg">
             @error('fecha')
                    <x-alert msg="{{ $message  }}" />
         @enderror
        </div>

        {{-- lesión --}}
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Lesión</label>
            <input type="text" wire:model.defer='lesion' class="form-control h-12 text-lg" placeholder="Esguince / Fractura / etc.">
         @error('lesion')
                    <x-alert msg="{{ $message  }}" />
         @enderror
        </div>

        {{-- parte afectada --}}
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Parte afectada</label>
            <input type="text" wire:model.defer='parte' class="form-control h-12 text-lg" placeholder="Rodilla / Tobillo / Hombro">
        @error('parte')
                    <x-alert msg="{{ $message  }}" />
         @enderror
        </div>

        {{-- gravedad --}}
        <div class="col-span-12 md:col-span-3">
                <label class="form-label text-base">Gravedad</label>
                <select wire:model.defer ="gravedad" class="form-select h-12 text-lg">
                    <option value="">Seleccione…</option>
                    <option>Leve</option>
                    <option>Moderada</option>
                    <option>Grave</option>
                </select>
                @error('gravedad')
                        <x-alert msg="{{ $message  }}" />
                @enderror
        </div>

        {{-- estado --}}
        <div class="col-span-12 md:col-span-3">
                <label class="form-label text-base">Estado</label>
                <select wire:model.defer ='estado' class="form-select h-12 text-lg">
                    <option value="">Seleccione…</option>
                    <option>Activa</option>
                    <option>Alta</option>
                    <option>En rehabilitación</option>
                </select>
                @error('estado')
                        <x-alert msg="{{ $message  }}" />
                @enderror
        </div>

        {{-- notas --}}
        <div class="col-span-12 md:col-span-9">
            <label class="form-label text-base">Notas</label>
            <input type="text" wire:model.defer='notas' class="form-control h-12 text-lg" placeholder="Observaciones / indicaciones médicas">
            @error('notas')
            <x-alert msg="{{ $message  }}" />
            @enderror
        </div>

        <div class="col-span-12 flex justify-end">
    @if($editModeLesion)
        <button type="button"
                class="btn btn-primary text-lg px-8 py-2.5"
                wire:click.prevent="updateLesion">
            Actualizar lesión
        </button>
        <button type="button"
                class="btn btn-outline-secondary text-lg px-8 py-2.5 ml-3"
                wire:click.prevent="resetLesionInputs">
            Cancelar
        </button>
    @else
        <button type="button"
                class="btn btn-primary text-lg px-8 py-2.5"
                wire:click.prevent="addLesion">
            Agregar lesión
        </button>
    @endif
</div>


        
    </div>

    {{-- Listado de lesiones registradas (placeholder) --}}
    <div class="overflow-x-auto">
        <table class="table text-base">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Lesión</th>
                    <th>Parte afectada</th>
                    <th>Gravedad</th>
                    <th>Estado</th>
                    <th>Notas</th>
                    <th>Acciones</th></tr>
            </thead>
            <tbody>
                 @forelse(($lesiones ?? []) as $lesion)
                <tr>
                    <td class="text-slate-400">{{ $lesion->fecha }}</td>
                    <td class="text-slate-400">{{ $lesion->lesion }}</td>
                    <td class="text-slate-400">{{ $lesion->parte }}</td>
                    <td class="text-slate-400">{{ $lesion->gravedad }}</td>
                    <td class="text-slate-400">{{ $lesion->estado }}</td>
                    <td class="text-slate-400">{{ $lesion->notas }}</td>
                    <td>
                        <div class="flex gap-2">
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    wire:click="editLesion({{ $lesion->id }})">
                                Editar
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    wire:click="deleteLesion({{ $lesion->id }})">
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-400">El alumno no registra lesiones</td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>
