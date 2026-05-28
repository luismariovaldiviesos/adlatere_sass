<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">

        {{-- FECHA Y HORA --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Fecha y Hora de la Audiencia</label>
            <input type="datetime-local" wire:model.defer="aud_fecha_hora" class="form-control h-12 text-lg">
            @error('aud_fecha_hora') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        {{-- TIPO DE AUDIENCIA --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Tipo de Audiencia</label>
            <input type="text" wire:model.defer="aud_tipo_audiencia" class="form-control h-12 text-lg"
                   placeholder="Ej: Formulación de Cargos, Preparatoria...">
            @error('aud_tipo_audiencia') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        {{-- ESTADO --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Estado</label>
            <select wire:model.defer="aud_estado" class="form-select h-12 text-lg">
                <option value="Programada">Programada</option>
                <option value="Realizada">Realizada</option>
                <option value="Suspendida">Suspendida</option>
                <option value="Fallida">Fallida</option>
            </select>
            @error('aud_estado') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        {{-- SALA / ENLACE --}}
        <div class="col-span-12 md:col-span-6">
            <label class="form-label text-base">Sala / Enlace</label>
            <input type="text" wire:model.defer="aud_sala_enlace" class="form-control h-12 text-lg"
                   placeholder="Ej: Sala 4 o https://meet.google.com/...">
        </div>

        {{-- ACTA / RESUMEN --}}
        <div class="col-span-12">
            <label class="form-label text-base">Acta / Resumen de Resultados</label>
            <textarea wire:model.defer="aud_acta_resumen" class="form-control text-lg" rows="4"
                      placeholder="Escribe aquí lo ocurrido en la audiencia (completar después si aún no se ha realizado)..."></textarea>
        </div>

        {{-- BOTONES --}}
        <div class="col-span-12 flex justify-end gap-3 mt-2">
            @if($editModeAudiencia)
                <button type="button" class="btn btn-outline-secondary text-lg px-8 py-2.5"
                        wire:click.prevent="cancelEditAudiencia">
                    Cancelar Edición
                </button>
                <button type="button" class="btn btn-primary text-lg px-8 py-2.5"
                        wire:click.prevent="saveAudiencia">
                    Actualizar Audiencia
                </button>
            @else
                <button type="button" class="btn btn-primary text-lg px-8 py-2.5"
                        wire:click.prevent="saveAudiencia">
                    Registrar Audiencia
                </button>
            @endif
        </div>
    </div>

    {{-- LISTADO DE AUDIENCIAS --}}
    @if(isset($juicio) && $juicio->audiencias->count() > 0)
    <div class="mt-8 border-t border-gray-200 pt-8">
        <h3 class="text-xl font-bold mb-4">Audiencias del Juicio</h3>
        <div class="overflow-x-auto">
            <table class="table table-report">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">FECHA Y HORA</th>
                        <th class="whitespace-nowrap">TIPO</th>
                        <th class="whitespace-nowrap">SALA / ENLACE</th>
                        <th class="whitespace-nowrap">ESTADO</th>
                        <th class="text-center whitespace-nowrap">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($juicio->audiencias->sortByDesc('fecha_hora') as $aud)
                    <tr class="intro-x">
                        <td class="font-medium whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i') }}
                        </td>
                        <td class="font-medium">{{ $aud->tipo_audiencia }}</td>
                        <td class="text-slate-500">{{ $aud->sala_enlace ?? '—' }}</td>
                        <td>
                            @php
                                $colores = [
                                    'Programada' => 'bg-blue-100 text-blue-800',
                                    'Realizada'  => 'bg-green-100 text-green-800',
                                    'Suspendida' => 'bg-yellow-100 text-yellow-800',
                                    'Fallida'    => 'bg-red-100 text-red-800',
                                ];
                                $color = $colores[$aud->estado] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $color }}">
                                {{ $aud->estado }}
                            </span>
                        </td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center gap-4">
                                <a class="flex items-center text-primary" href="javascript:;"
                                   wire:click="editAudiencia({{ $aud->id }})">
                                    <i data-lucide="edit" class="w-4 h-4 mr-1"></i> Editar
                                </a>
                                <a class="flex items-center text-danger" href="javascript:;"
                                   onclick="confirm('¿Seguro que desea eliminar esta audiencia?') || event.stopImmediatePropagation()"
                                   wire:click="destroyAudiencia({{ $aud->id }})">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>