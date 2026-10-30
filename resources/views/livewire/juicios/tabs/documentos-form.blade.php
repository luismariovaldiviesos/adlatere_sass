<div class="grid grid-cols-12 gap-4 items-end">
    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-sm font-medium">Tipo de documento</label>
        <select wire:model.defer="doc_tipo_documento_id" class="form-select">
            <option value="">Seleccione...</option>
            @foreach($docTipos as $tipo)
                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
            @endforeach
        </select>
        @error('doc_tipo_documento_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-sm font-medium">Vincular a</label>
        <select wire:model="doc_vinculo_tipo" class="form-select">
            <option value="expediente">Expediente completo</option>
            <option value="actividad">Actividad / escrito</option>
            <option value="audiencia">Audiencia</option>
            <option value="consulta">Consulta</option>
        </select>
        @error('doc_vinculo_tipo') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    @if($doc_vinculo_tipo === 'actividad')
    <div class="col-span-12 md:col-span-6" wire:key="doc-target-actividad">
        <label class="form-label text-sm font-medium">Actividad</label>
        <select wire:model.defer="doc_vinculo_id" class="form-select">
            <option value="">Seleccione la actividad...</option>
            @foreach($docActividades as $actividad)
                <option value="{{ $actividad->id }}">
                    #{{ $actividad->id }} · {{ $actividad->tipoActividad->nombre ?? 'Actividad' }} ·
                    {{ \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y') }} ·
                    {{ \Illuminate\Support\Str::limit($actividad->descripcion ?: 'Sin descripción', 55) }}
                </option>
            @endforeach
        </select>
        @error('doc_vinculo_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>
    @elseif($doc_vinculo_tipo === 'audiencia')
    <div class="col-span-12 md:col-span-6" wire:key="doc-target-audiencia">
        <label class="form-label text-sm font-medium">Audiencia</label>
        <select wire:model.defer="doc_vinculo_id" class="form-select">
            <option value="">Seleccione la audiencia...</option>
            @foreach($docAudiencias as $audiencia)
                <option value="{{ $audiencia->id }}">
                    #{{ $audiencia->id }} · {{ $audiencia->tipo_audiencia }} ·
                    {{ \Carbon\Carbon::parse($audiencia->fecha_hora)->format('d/m/Y H:i') }}
                </option>
            @endforeach
        </select>
        @error('doc_vinculo_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>
    @elseif($doc_vinculo_tipo === 'consulta')
    <div class="col-span-12 md:col-span-6" wire:key="doc-target-consulta">
        <label class="form-label text-sm font-medium">Consulta</label>
        <select wire:model.defer="doc_vinculo_id" class="form-select">
            <option value="">Seleccione la consulta...</option>
            @foreach($docConsultas as $consulta)
                <option value="{{ $consulta->id }}">#{{ $consulta->id }} · {{ $consulta->asunto->nombre ?? 'Consulta' }}</option>
            @endforeach
        </select>
        @error('doc_vinculo_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>
    @else
    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-sm font-medium">Alcance</label>
        <div class="h-10 flex items-center px-3 rounded bg-slate-100 text-slate-600 text-sm">
            Se guardará en el expediente sin vínculo específico.
        </div>
    </div>
    @endif

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-sm font-medium">Etapa procesal</label>
        <select wire:model.defer="doc_estado_procesal_id" class="form-select">
            <option value="">Sin etapa</option>
            @foreach($estados_procesales as $estado)
                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
            @endforeach
        </select>
        @error('doc_estado_procesal_id') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="col-span-12 md:col-span-5">
        <label class="form-label text-sm font-medium">Nombre / descripción corta</label>
        <input type="text" wire:model.defer="doc_nombre" class="form-control" placeholder="Ej: Poder notariado del actor">
        @error('doc_nombre') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-sm font-medium">Archivo (máx. 10 MB)</label>
        <input type="file" wire:model="doc_archivo" class="form-control pt-2" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
        <div wire:loading wire:target="doc_archivo" class="text-primary mt-1 text-xs font-medium">Cargando archivo...</div>
        @error('doc_archivo') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="col-span-12">
        <label class="form-label text-sm font-medium">Nota interna <span class="text-slate-400">(opcional)</span></label>
        <textarea wire:model.defer="doc_descripcion" rows="2" class="form-control" placeholder="Ej: Se adjunta copia para respaldo del escrito presentado."></textarea>
        @error('doc_descripcion') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="col-span-12 flex justify-end">
        <button type="button" class="btn btn-primary px-6" wire:click="saveDocumentoGeneral" wire:loading.attr="disabled" wire:target="doc_archivo,saveDocumentoGeneral">
            <span wire:loading.remove wire:target="doc_archivo,saveDocumentoGeneral"><i class="fas fa-upload mr-1"></i> Subir documento</span>
            <span wire:loading wire:target="doc_archivo,saveDocumentoGeneral"><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
        </button>
    </div>
</div>
