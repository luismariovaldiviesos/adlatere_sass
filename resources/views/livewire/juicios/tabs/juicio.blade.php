<div class="p-8 grid grid-cols-12 gap-6">
    <div class="col-span-12 md:col-span-12 grid grid-cols-12 gap-6">
        
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold"># Proceso SATJE</label>
            <input type="text" wire:model='cod_satje' class="form-control h-12 text-lg" placeholder="01333-202X-XXXXX">
            @error('cod_satje') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Fecha de Inicio / Sorteo</label>
            <input type="date" wire:model='fecha_inicio' class="form-control h-12 text-lg">
            @error('fecha_inicio') <x-alert msg="{{ $message }}" /> @enderror
        </div>
           <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Estado Procesal</label>
            <select wire:model.live='estado_procesal_id' class="form-select h-12 text-lg">
                <option value="">Seleccione Estado...</option>
                @foreach($estados_procesales as $e)
                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                @endforeach
            </select>
             @error('estado_procesal_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base font-bold">Prioridad</label>
            <select wire:model='prioridad' class="form-select h-12 text-lg">
                <option value="Baja">Baja</option>
                <option value="Media">Media (Estándar)</option>
                <option value="Alta">Alta</option>
                <option value="Urgente">Urgente</option>
            </select>
            @error('prioridad') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 border-t border-gray-200 my-2"></div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Materia</label>
            <select wire:model.live='materia_id' class="form-select h-12 text-lg">
                <option value="">Seleccione Materia...</option>
                @foreach($materias as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Procedimiento</label>
            <select wire:model.live='procedimiento_id' class="form-select h-12 text-lg" {{ empty($procedimientos) ? 'disabled' : '' }}>
                <option value="">Seleccione Procedimiento...</option>
                @foreach($procedimientos as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold">Asunto Específico</label>
            <select wire:model='asunto_id' class="form-select h-12 text-lg" {{ empty($asuntos) ? 'disabled' : '' }}>
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
    </div>
</div>