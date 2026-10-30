@php
    $soloLectura = !($puedeEditarBase ?? false);
@endphp
<div class="p-8 grid grid-cols-12 gap-6">
    @if($soloLectura)
    <div class="col-span-12">
        <div class="alert alert-info" role="alert">
            @if(isset($juicio) && $juicio && $juicio->base_confirmada_at)
            <strong>Base confirmada:</strong> la carátula y los sujetos procesales son inmutables. Solo se puede actualizar el estado procesal (Administrador y abogado dueño) y la prioridad (solo Administrador).
            @else
            <strong>Solo lectura:</strong> no tiene permiso para modificar la base de este juicio.
            @endif
        </div>
    </div>
    @endif
    <div class="col-span-12 md:col-span-12 grid grid-cols-12 gap-6">
        
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold"># Proceso SATJE</label>
            <input type="text" wire:model.defer='cod_satje' class="form-control h-12 text-lg" placeholder="01333-202X-XXXXX" {{ $soloLectura ? 'disabled' : '' }}>
            @error('cod_satje') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Fecha de Inicio / Sorteo</label>
            <input type="date" wire:model.defer='fecha_inicio' class="form-control h-12 text-lg" {{ $soloLectura ? 'disabled' : '' }}>
            @error('fecha_inicio') <x-alert msg="{{ $message }}" /> @enderror
        </div>
           <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Estado Procesal</label>
            <select wire:model.defer='estado_procesal_id' class="form-select h-12 text-lg" {{ $puedeEditarEstadoProcesal ? '' : 'disabled' }}>
                <option value="">Seleccione Estado...</option>
                @foreach($estados_procesales as $e)
                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                @endforeach
            </select>
             @error('estado_procesal_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Prioridad</label>
            <select wire:model.defer='prioridad' class="form-select h-12 text-lg" {{ $puedeEditarPrioridadCaratula ? '' : 'disabled' }}>
                <option value="Baja">Baja</option>
                <option value="Media">Media (Estándar)</option>
                <option value="Alta">Alta</option>
                <option value="Urgente">Urgente</option>
            </select>
            @error('prioridad') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 border-t border-gray-200 my-2"></div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Provincia</label>
            <select wire:model='provincia_id' class="form-select h-12 text-lg" {{ $soloLectura ? 'disabled' : '' }}>
                <option value="">Seleccione Provincia...</option>
                @foreach($provincias as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
            @error('provincia_id') <span class="text-theme-6 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Cantón</label>
            <select wire:model='canton_id' class="form-select h-12 text-lg" {{ $soloLectura || empty($cantones) ? 'disabled' : '' }}>
                <option value="">Seleccione Cantón...</option>
                @foreach($cantones as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
            @error('canton_id') <span class="text-theme-6 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Unidad Judicial</label>
            <select wire:model='unidad_id' class="form-select h-12 text-lg" {{ $soloLectura || empty($unidades_judiciales) ? 'disabled' : '' }}>
                <option value="">Seleccione Unidad...</option>
                @foreach($unidades_judiciales as $u)
                    <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                @endforeach
            </select>
            @error('unidad_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 border-t border-gray-200 my-2"></div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Materia</label>
            <select wire:model='materia_id' class="form-select h-12 text-lg" {{ $soloLectura ? 'disabled' : '' }}>
                <option value="">Seleccione Materia...</option>
                @foreach($materias as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
            @error('materia_id') <span class="text-theme-6 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Procedimiento</label>
            <select wire:model='procedimiento_id' class="form-select h-12 text-lg" {{ $soloLectura || empty($procedimientos) ? 'disabled' : '' }}>
                <option value="">Seleccione Procedimiento...</option>
                @foreach($procedimientos as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
            @error('procedimiento_id') <span class="text-theme-6 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Asunto Específico</label>
            <select wire:model.defer='asunto_id' class="form-select h-12 text-lg" {{ $soloLectura || empty($asuntos) ? 'disabled' : '' }}>
                <option value="">Seleccione Asunto...</option>
                @foreach($asuntos as $a)
                    <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                @endforeach
            </select>
            @error('asunto_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12">
            <div class="alert alert-outline-secondary flex items-center mb-2" role="alert">
                <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                <span>El juicio se registrará inicialmente en estado: <strong>Calificación / Ingreso</strong></span>
            </div>
        </div>

        @if(!$soloLectura || $puedeEditarEstadoProcesal || $puedeEditarPrioridadCaratula)
        <div class="col-span-12 flex justify-end mt-4">
            @if($editModeJuicio)
                <button type="button" 
                        class="btn btn-primary text-lg px-10 py-3 shadow-md" 
                        wire:click.prevent="saveJuicio">
                    <i class="fas fa-save mr-2"></i> Actualizar Carátula
                </button>
            @else
                <button type="button" 
                        class="btn btn-primary text-lg px-10 py-3 shadow-md" 
                        wire:click.prevent="saveJuicio">
                    <i class="fas fa-check mr-2"></i> Registrar y Continuar
                </button>
            @endif
        </div>
        @endif
    </div>
</div>