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
                        <th class="whitespace-nowrap">ACTA</th>
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
                        <td class="font-medium">
                            @if($aud->acta_resumen != null)
                            <div class="flex gap-3">
                                <button wire:click="descargarWord({{ $aud->id }})" 
                                        wire:loading.attr="disabled"
                                        title="Descargar Acta en Word"
                                        class="p-2.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 transition-all duration-200 shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>

                                <button wire:click="descargarPdf({{ $aud->id }})" 
                                        wire:loading.attr="disabled"
                                        title="Descargar Acta en PDF"
                                        class="p-2.5 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-white border border-danger/20 transition-all duration-200 shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
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