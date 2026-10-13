<div class="intro-y box p-5">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-lg font-medium">{{ $action }} tarea</h2>
        <button wire:click.prevent="CloseModal" class="btn btn-outline-secondary">Volver</button>
    </div>

    @if($puedeGestionar)
    <div class="grid grid-cols-12 gap-4 gap-y-3">
        <div class="col-span-12">
            <label class="form-label">Título *</label>
            <input type="text" wire:model.defer="titulo" class="form-control" maxlength="255">
            @error('titulo') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12">
            <label class="form-label">Descripción / instrucciones</label>
            <textarea wire:model.defer="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Tipo *</label>
            <select wire:model="tipo" class="form-select">
                <option value="externa">Externa (diligencia física)</option>
                <option value="juicio">Vinculada a juicio</option>
            </select>
        </div>

        @if($tipo === 'juicio')
        <div class="col-span-12 sm:col-span-8 relative">
            <label class="form-label">Juicio vinculado *</label>
            <input type="text" wire:model.debounce.400ms="searchJuicio" class="form-control"
                placeholder="Buscar por SATJE o asunto..." autocomplete="off">
            @if($showJuicioDropdown && count($juicios) > 0)
            <div class="absolute z-50 bg-white border rounded shadow w-full mt-1 max-h-48 overflow-y-auto">
                @foreach($juicios as $j)
                <div wire:click="selectJuicio({{ $j->id }}, '{{ addslashes($j->cod_satje) }}')"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                    <strong>{{ $j->cod_satje }}</strong>
                    <small class="text-gray-500">({{ $j->asunto->nombre ?? '' }})</small>
                </div>
                @endforeach
            </div>
            @endif
            @error('juicio_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        @endif

        <div class="col-span-12 sm:col-span-6">
            <label class="form-label">Asignar a (abogado o pasante) *</label>
            <select wire:model.defer="asignado_a" class="form-select">
                <option value="">Seleccione...</option>
                @foreach($abogados as $a)
                <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->profile }})</option>
                @endforeach
            </select>
            @error('asignado_a') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="col-span-12 sm:col-span-6">
            <label class="form-label">Fecha límite</label>
            <input type="date" wire:model.defer="fecha_limite" class="form-control">
        </div>
    </div>

    <div class="text-right mt-5">
        <button wire:click.prevent="CloseModal" class="btn btn-outline-secondary mr-1">Cancelar</button>
        <button wire:click.prevent="Store" class="btn btn-primary">Guardar</button>
    </div>
    @else
    <div class="bg-gray-100 rounded p-3 text-sm">
        <strong>{{ $titulo }}</strong>
        <p class="mt-1">{{ $descripcion }}</p>
        <p class="mt-1 text-gray-500">Use los botones de estado en la tabla para avanzar esta tarea.</p>
    </div>
    <div class="text-right mt-5">
        <button wire:click.prevent="CloseModal" class="btn btn-outline-secondary mr-1">Volver</button>
    </div>
    @endif

    {{-- HOJA PRIVADA: redacción + adjuntos (solo asignado y admin) --}}
    @if($puedeVerPrivado)
    <div class="mt-6 border-t pt-5">
        <h3 class="text-base font-bold mb-3">Hoja de trabajo (privada)</h3>
        <label class="form-label">Redacción</label>
        <div wire:ignore class="document-editor" x-data="{}"
            x-on:set-tarea-editor-content.window="window.tareaPendingContent = $event.detail.content || ''; try { if(window.tareaEditorInstance && window.tareaEditorInstance.root.isConnected){ window.tareaEditorInstance.root.innerHTML = window.tareaPendingContent; window.tareaPendingContent = null; } } catch(e){}">
            <div id="toolbar-tarea-container"></div>
            <div class="editable-container">
                <div id="editor-tarea-hoja">{!! $contenido ?? '' !!}</div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
            <button type="button" class="btn btn-outline-secondary"
                onclick="if(typeof loadTareaQuillEditor==='function') loadTareaQuillEditor();">
                <i class="fas fa-pen"></i> Activar editor
            </button>
            <button type="button" wire:click.prevent="guardarContenido" class="btn btn-success text-white">
                <i class="fas fa-save"></i> Guardar redacción
            </button>
        </div>

        <div class="mt-5">
            <label class="form-label">Adjuntos</label>
            <div class="flex flex-wrap gap-2 items-end">
                <input type="file" wire:model="adjunto" class="form-control" style="max-width:320px;">
                <button type="button" wire:click.prevent="subirAdjunto" class="btn btn-primary">Subir</button>
            </div>
            @error('adjunto') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            <div wire:loading wire:target="adjunto" class="text-primary text-sm mt-1">Subiendo...</div>
            <ul class="mt-3 space-y-2">
                @forelse($adjuntos as $adj)
                <li class="flex items-center gap-2 text-sm bg-gray-50 border rounded px-3 py-2">
                    <i class="fas fa-paperclip text-gray-400"></i>
                    <span class="font-medium">{{ $adj['nombre'] }}</span>
                    <a href="{{ route('tenant.media', ['path' => $adj['ruta_archivo']]) }}" target="_blank" class="btn btn-outline-primary btn-sm ml-auto">Ver</a>
                    <button type="button" wire:click.prevent="quitarAdjunto({{ $adj['id'] }})" class="btn btn-outline-danger btn-sm">Quitar</button>
                </li>
                @empty
                <li class="text-sm text-gray-400 italic">Sin adjuntos.</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif
</div>

<script>
    function loadTareaQuillEditor() {
        if (typeof Quill !== 'undefined') { startTareaWordEditor(); return; }
        if (!document.querySelector('#quill-css')) {
            let link = document.createElement('link');
            link.id = 'quill-css'; link.rel = 'stylesheet';
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
        script.addEventListener('load', () => { startTareaWordEditor(); });
    }
    function startTareaWordEditor() {
        const editorDom = document.querySelector('#editor-tarea-hoja');
        if (!editorDom) return;
        if (window.tareaEditorInstance) {
            try {
                if (!window.tareaEditorInstance.root.isConnected) { window.tareaEditorInstance = null; }
            } catch(e) { window.tareaEditorInstance = null; }
        }
        if (!editorDom.classList.contains('ql-container')) {
            var quill = new Quill('#editor-tarea-hoja', {
                theme: 'snow',
                placeholder: 'Redacte aquí el trabajo de la tarea...',
                modules: { toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['clean']
                ]}
            });
            const toolbarContainer = document.querySelector('#toolbar-tarea-container');
            const quillToolbar = document.querySelector('#toolbar-tarea-container ~ .editable-container .ql-toolbar, .editable-container .ql-toolbar');
            if (toolbarContainer && quillToolbar) {
                toolbarContainer.appendChild(quillToolbar);
            }
            window.tareaEditorInstance = quill;
            quill.on('text-change', function() { @this.set('contenido', quill.root.innerHTML); });
        }
        if (window.tareaPendingContent !== undefined && window.tareaPendingContent !== null && window.tareaEditorInstance) {
            try {
                window.tareaEditorInstance.root.innerHTML = window.tareaPendingContent;
                window.tareaPendingContent = null;
            } catch(e) {}
        }
    }
    setTimeout(() => { loadTareaQuillEditor(); }, 100);
</script>

<style>
    .document-editor { border: 1px solid #cbd5e1; background: white; display: flex; flex-direction: column; border-radius: 0.5rem; overflow: hidden; height: 500px; }
    .editable-container { flex-grow: 1; overflow-y: auto; background: #f1f5f9; padding: 30px 10px; }
    #editor-tarea-hoja { width: 21cm; min-height: 16cm; margin: 0 auto; padding: 2cm; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); color: black; font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; }
    .ql-container.ql-snow { border: none !important; }
    .ql-editor { padding: 0 !important; min-height: 100%; }
    #toolbar-tarea-container { background: #f8fafc !important; border-bottom: 1px solid #cbd5e1; }
    .ql-toolbar.ql-snow { border: none !important; padding: 10px !important; }
</style>
