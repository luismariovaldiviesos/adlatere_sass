<div class="p-8 space-y-6">
    <div class="grid grid-cols-12 gap-6 items-end">
        
        {{-- CAMBIO DE ESTADO PROCESAL (Requerimiento Técnico) --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base font-bold text-primary text-xl">¿Cambiar Estado Procesal?</label>
            <select wire:model.defer="nuevo_estado_id" class="form-select h-12 text-lg border-primary">
                <option value="">Mantener estado actual...</option>
                @foreach($estados_procesales as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- TIPO ACTIVIDAD (Vinculado a Plantillas) --}}
        <div class="col-span-12 md:col-span-4 flex items-end gap-2">
            <div class="flex-1">
                <label class="form-label text-base">Tipo Actividad</label>
                <select wire:model="tipo_actividad_id" class="form-select h-12 text-lg">
                    <option value="">Seleccione…</option>
                    @foreach($tipos_actividades as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
                @error('tipo_actividad_id') <x-alert msg="{{ $message }}" /> @enderror
            </div>
            
            <div class="col-span-12 md:col-span-6 flex items-end gap-2">
                @if($tiene_plantilla_disponible)
                    @if(count($plantillas_disponibles) > 1)
                        <div class="flex-grow">
                            <label class="form-label font-bold text-base text-warning">Seleccione la Plantilla a Cargar</label>
                            <select wire:model.defer="plantilla_seleccionada_id" class="form-select h-12 text-lg border-warning">
                                <option value="">Seleccione una plantilla...</option>
                                @foreach($plantillas_disponibles as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="button" wire:click.prevent="cargarPlantilla" class="btn btn-warning h-12 px-4 shadow-md text-white font-bold whitespace-nowrap {{ count($plantillas_disponibles) > 1 ? '' : 'mt-auto' }}" title="Cargar plantilla seleccionada al editor">
                        Cargar Plantilla
                    </button>
                @endif
            </div>
        </div>

        {{-- ORIGEN --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Origen Actividad</label>
            <select wire:model="origen" class="form-select h-12 text-lg">
                <option value="Interno">Interno </option>
                <option value="Externo">Externo </option>
            </select>
        </div>

        {{-- FECHA --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Fecha de Actividad</label>
            <input wire:model.defer="fecha_actividad" type="datetime-local" class="form-control h-12 text-lg">
            @error('fecha_actividad') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        {{-- DESCRIPCIÓN CORTA --}}
        <div class="col-span-12 md:col-span-8">
            <label class="form-label text-base">Resumen / Descripción</label>
            <input type="text" wire:model.defer="descripcion" class="form-control h-12 text-lg" placeholder="Breve nota sobre la actuación">
        </div>

        {{-- ZONA DEL EDITOR DE PLANTILLA (ESTILO WORD) --}}
        <div class="col-span-12 mt-4">
            <label class="form-label text-base font-bold">Documento de la Actividad</label>
            <div wire:ignore class="document-editor" x-data="{}" x-on:set-actividad-editor-content.window="if(window.actividadEditorInstance) { window.actividadEditorInstance.root.innerHTML = $event.detail.content || ''; }">
                <div id="toolbar-actividad-container" class="border-b border-gray-300"></div>
                <div class="editable-container">
                    <div id="editor-actividad-hoja">
                        {!! $contenido !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- CARGA DE ARCHIVO (Si es Externo) --}}
        <div class="col-span-12 mt-4" x-show="$wire.origen == 'Externo'">
            <label class="form-label text-base font-bold">Cargar Documento Externo (PDF)</label>
            <input type="file" wire:model="archivo" class="form-control h-12">
        </div>

        <div class="col-span-12 flex justify-end mt-6">
            @if($editModeActividad)
                <button type="button" class="btn btn-outline-secondary text-lg px-8 py-2.5 mr-2" wire:click.prevent="cancelEditActividad">
                    Cancelar Edición
                </button>
                <button type="button" class="btn btn-primary text-lg px-8 py-2.5" wire:click.prevent="addActividad">
                    <i class="fas fa-save mr-2"></i> Actualizar Actuación
                </button>
            @else
                <button type="button" class="btn btn-primary text-lg px-8 py-2.5" wire:click.prevent="addActividad">
                    <i class="fas fa-plus mr-2"></i> Registrar Actuación y Actualizar Juicio
                </button>
            @endif
        </div>
    </div>

    {{-- DEBUG ABSOLUTO (SIEMPRE VISIBLE - FUERA DE CUALQUIER @if) --}}
    @php
        $hayJuicio = isset($juicio);
        $juicioId = $hayJuicio ? $juicio->id : 'NULL';
        $actsCount = $hayJuicio ? $juicio->actividades->count() : 0;
        $actsLoaded = $hayJuicio && $juicio->relationLoaded('actividades') ? 'SÍ' : 'NO';
        $userId = auth()->id();
        $esAbogado = $hayJuicio ? $juicio->abogados()->where('user_id', $userId)->whereRaw("TRIM(LOWER(rol_en_juicio)) LIKE ?", ['%abogado%patrocinador%'])->exists() : false;
    @endphp

    <div style="background:#e7f3ff;border:2px solid #0d6efd;padding:15px;margin:15px 0;font-family:monospace;font-size:12px;color:#084298;">
    <strong>DEBUG ABSOLUTO:</strong>
    Juicio existe: {{ $hayJuicio ? 'SÍ' : 'NO' }} | ID: {{ $juicioId }}
    | Actividades: {{ $actsCount }} | Loaded: {{ $actsLoaded }}
    | User: {{ $userId }} | EsAbogadoPatro: {{ $esAbogado ? 'SÍ' : 'NO' }}
    @if($hayJuicio && $actsCount > 0)
        @foreach($juicio->actividades->sortByDesc('fecha_actividad') as $a)
            <br>ID: {{ $a->id }} | {{ $a->tipoActividad->nombre ?? 'SIN TIPO' }} | Firmable: {{ $a->tipoActividad->es_firmable ? 'SÍ' : 'NO' }} | Estado: {{ $a->estado_firma }}
        @endforeach
    @endif
    </div>

    {{-- LISTADO (solo si hay actividades) --}}
    @if(isset($juicio) && $juicio->actividades->count() > 0)
    <div class="mt-8 border-t border-gray-200 pt-8">
        <h3 class="text-xl font-bold mb-4">Historial de Actividades</h3>
        <div class="overflow-x-auto">
            <table class="table table-report">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">CREADA</th>
                        <th class="whitespace-nowrap">TIPO</th>
                        <th class="whitespace-nowrap">ORIGEN</th>
                        <th class="whitespace-nowrap">DESCRIPCIÓN</th>
                        <th class="text-center whitespace-nowrap">MODIFICADA</th>
                        <th class="text-center whitespace-nowrap">ACCIONES</th>
                        <th class="text-center whitespace-nowrap">FIRMAR</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $actividades = $juicio->actividades->sortByDesc('fecha_actividad');
                        $userId = auth()->id();
                        $esAbogadoPatro = $juicio->abogados()->where('user_id', $userId)->whereRaw("TRIM(LOWER(rol_en_juicio)) LIKE ?", ['%abogado%patrocinador%'])->exists();
                    @endphp
                    
                    @foreach($actividades as $act)
                    <tr class="intro-x">
                        <td class="font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($act->fecha_actividad)->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap font-medium">{{ $act->tipoActividad->nombre ?? 'N/A' }}</td>
                        <td>
                            @if($act->origen == 'Interno')
                                <span class="text-blue-600 bg-blue-100 px-2 py-1 rounded text-xs font-bold uppercase">Interno</span>
                            @else
                                <span class="text-orange-600 bg-orange-100 px-2 py-1 rounded text-xs font-bold uppercase">Externo</span>
                            @endif
                        </td>
                        <td class="text-slate-500">{{ $act->descripcion }}</td>
                        <td class="text-slate-500">{{ \Carbon\Carbon::parse($act->updated_at)->format('d/m/Y H:i') }}</td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center gap-2 flex-wrap">
                                <a class="flex items-center mr-3 text-primary" href="javascript:;" wire:click="editActividad({{ $act->id }})">
                                    <i data-lucide="edit" class="w-4 h-4 mr-1"></i> Editar
                                </a>
                                <a class="flex items-center text-danger" href="javascript:;" onclick="confirm('¿Está seguro de eliminar esta actividad?') || event.stopImmediatePropagation()" wire:click="destroyActividad({{ $act->id }})">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Eliminar
                                </a>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $esFirmable = $act->tipoActividad->es_firmable ?? false;
                                $estadoFirma = strtolower($act->estado_firma ?? 'no_requerida');
                                $yaFirmada = $estadoFirma === 'firmada';
                                $puedeFirmar = $esFirmable && !$yaFirmada && $esAbogadoPatro;
                            @endphp

                            @if($yaFirmada)
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-green-600 bg-green-100 px-2 py-1 rounded text-xs font-bold uppercase">
                                        <i class="fas fa-check-circle mr-1"></i> Firmada
                                    </span>
                                    @if($act->pdf_firmado_path)
                                       <a href="{{ route('tenant.media', ['path' => 'actividades/firmados/' . $act->pdf_firmado_path]) }}" target="_blank" class="btn btn-sm btn-primary text-white shadow-md" title="Ver Documento Firmado">
                                            <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                                        </a>
                                    @endif
                                </div>
                            @elseif($puedeFirmar)
                                <button wire:click="abrirModalFirmar({{ $act->id }})" class="btn btn-sm btn-warning" title="Firmar digitalmente">
                                    <i data-lucide="signature" class="w-4 h-4 mr-1"></i> Firmar
                                </button>
                            @elseif($esFirmable)
                                <span class="badge badge-warning badge-outline text-xs" title="Solo abogado patrocinador puede firmar">
                                    <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Solo abogado
                                </span>
                            @else
                                <span class="text-gray-500 text-xs">No firmable</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="alert alert-info text-center py-8 mt-8">
            <i data-lucide="info" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
            <p class="text-gray-500">No hay actividades registradas. Crea una arriba.</p>
        </div>
    @endif
</div>

{{-- MODAL FIRMAR (FUERA DE TODO) --}}
@if($showFirmarModal)
<div class="fixed top-0 left-0 w-full h-full z-50 flex items-center justify-center" style="background: rgba(0,0,0,0.6);">
    <div class="bg-white rounded-lg p-6 relative shadow-2xl overflow-y-auto" style="width: 90%; max-width: 500px;">
        <h3 class="font-bold text-xl mb-4 text-gray-800 border-b pb-2">Firmar Actividad Digitalmente</h3>
        <p class="text-sm text-gray-600 mb-4">
            <strong>{{ $actividadAFirmar->tipoActividad->nombre }}</strong><br>
            {{ $actividadAFirmar->descripcion }}<br>
            <small>Fecha: {{ $actividadAFirmar->fecha_actividad->format('d/m/Y H:i') }}</small>
        </p>
        <div class="mt-4">
            <label class="font-bold text-gray-700 block mb-2">Confirmar contraseña del certificado</label>
            <input type="password" wire:model="firmaPasswordConfirm" class="form-control w-full h-10" autocomplete="new-password">
            @error('firmaPasswordConfirm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="mt-6 flex justify-end">
            <button wire:click="$set('showFirmarModal', false)" class="btn btn-outline-secondary mr-2 px-4 py-2">Cancelar</button>
            <button wire:click="confirmarFirma" class="btn btn-primary px-4 py-2">
                <i class="fas fa-signature mr-2"></i> Firmar y Guardar
            </button>
        </div>
    </div>
</div>
@endif

{{-- Scripts para manejar el editor Quill --}}
<script>
    function loadActividadQuillEditor() {
        if (typeof Quill !== 'undefined') {
            startActividadWordEditor();
            return;
        }

        if (!document.querySelector('#quill-css')) {
            let link = document.createElement('link');
            link.id = 'quill-css';
            link.rel = 'stylesheet';
            link.href = 'https://cdn.quilljs.com/1.3.6/quill.snow.css';
            document.head.appendChild(link);
        }

        let script = document.querySelector('#quill-script');
        if (!script) {
            script = document.createElement('script');
            script.id = 'quill-script';
            script.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
            document.head.appendChild(script);
        }

        script.addEventListener('load', () => {
            startActividadWordEditor();
        });
    }

    function startActividadWordEditor() {
        const editorDom = document.querySelector('#editor-actividad-hoja');
        if (!editorDom) return;
        if (editorDom.classList.contains('ql-container')) return;

        var quill = new Quill('#editor-actividad-hoja', {
            theme: 'snow',
            placeholder: 'Redacte el documento aquí...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['clean']
                ]
            }
        });

        const toolbarContainer = document.querySelector('#toolbar-actividad-container');
        const quillToolbar = document.querySelector('.ql-toolbar');
        if (toolbarContainer && quillToolbar) {
            toolbarContainer.appendChild(quillToolbar);
        }

        window.actividadEditorInstance = quill;

        quill.on('text-change', function() {
            @this.set('contenido', quill.root.innerHTML);
        });
    }

      setTimeout(() => {
        loadActividadQuillEditor();
    }, 100);
</script>

<style>
    .document-editor { border: 1px solid #cbd5e1; background: white; display: flex; flex-direction: column; border-radius: 0.5rem; overflow: hidden; height: 800px; }
    .editable-container { flex-grow: 1; overflow-y: auto; background: #f1f5f9; padding: 40px 10px; }
    #editor-actividad-hoja { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 2.5cm; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); color: black; font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; }
    .ql-container.ql-snow { border: none !important; }
    .ql-editor { padding: 0 !important; min-height: 100%; }
    #toolbar-actividad-container { background: #f8fafc !important; border-bottom: 1px solid #cbd5e1; }
    .ql-toolbar.ql-snow { border: none !important; padding: 10px !important; }
</style>