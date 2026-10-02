<div class="intro-y box p-5">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-lg font-medium">{{ $action }} consulta</h2>
        <button wire:click.prevent="CloseModal" class="btn btn-outline-secondary">Volver</button>
    </div>

    <div class="grid grid-cols-12 gap-4 gap-y-3">
    @if($puedeAdministrar)
        {{-- BLOQUE RECEPCIÓN: datos + cobro (el abogado no ve ni toca esto) --}}
        {{-- CLIENTE con buscador --}}
        <div class="col-span-12 sm:col-span-6 relative">
            <label class="form-label">Cliente *</label>
            <input type="text" wire:model.debounce.400ms="searchCustomer" class="form-control"
                placeholder="Buscar por nombre o identificación..." autocomplete="off">
            @if($showDropdown && count($customers) > 0)
            <div class="absolute z-50 bg-white border rounded shadow w-full mt-1 max-h-48 overflow-y-auto">
                @foreach($customers as $c)
                <div wire:click="selectCustomer({{ $c->id }}, '{{ addslashes($c->businame) }}')"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                    <strong>{{ $c->businame }}</strong>
                    <small class="text-gray-500">({{ $c->valueidenti }})</small>
                </div>
                @endforeach
            </div>
            @endif
            @error('customer_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- ABOGADO --}}
        <div class="col-span-12 sm:col-span-6">
            <label class="form-label">Abogado asignado</label>
            <select wire:model.defer="abogado_id" class="form-select">
                <option value="">Sin asignar...</option>
                @foreach($abogados as $a)
                <option value="{{ $a->id }}">{{ $a->name }}</option>
                @endforeach
            </select>
            @error('abogado_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- CADENA MATERIA / PROCEDIMIENTO / ASUNTO --}}
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Materia</label>
            <select wire:model="materia_id" class="form-select">
                <option value="">Seleccione...</option>
                @foreach($materias as $m)
                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Procedimiento</label>
            <select wire:model="procedimiento_id" class="form-select">
                <option value="">Seleccione...</option>
                @foreach($procedimientos as $p)
                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Asunto *</label>
            <select wire:model.defer="asunto_id" class="form-select">
                <option value="">Seleccione...</option>
                @foreach($asuntos as $a)
                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                @endforeach
            </select>
            @error('asunto_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- COSTO --}}
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Costo de la consulta ($) *</label>
            <input type="number" wire:model.defer="costo" class="form-control" step="0.01" min="0">
            @error('costo') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- CONDICIÓN DE PAGO (se define al crear, como caja médica) --}}
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Condición de pago *</label>
            <select wire:model="estado_pago" class="form-select">
                <option value="pendiente">Pendiente de pago</option>
                <option value="pagada">Pagada</option>
                <option value="no_factura">No se cobra (cortesía)</option>
                <option value="facturada" disabled>Facturada (automático)</option>
            </select>
        </div>
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Fecha de pago</label>
            <input type="date" wire:model.defer="fecha_pago" class="form-control">
        </div>
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Método</label>
            <select wire:model.defer="metodo_pago" class="form-select">
                <option>Efectivo</option>
                <option>Transferencia</option>
                <option>Tarjeta</option>
                <option>Otro</option>
            </select>
        </div>
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Comprobante (opcional)</label>
            <input type="file" wire:model="comprobante" class="form-control">
        </div>
        @if($selected_id == 0)
        <div class="col-span-12 sm:col-span-4 flex items-end gap-2 pb-1">
            <input type="checkbox" wire:model="facturar_ahora" id="facturarAhora" class="form-check-input">
            <label for="facturarAhora" class="text-sm">Crear factura ahora (solo si está pagada)</label>
        </div>
        @endif
    @else
        <div class="col-span-12 bg-gray-100 rounded p-3 text-sm">
            <strong>Cliente:</strong> {{ $cliente_nombre }} &nbsp;|&nbsp;
            <strong>Asunto:</strong> {{ $asuntos->firstWhere('id', $asunto_id)->nombre ?? '' }} &nbsp;|&nbsp;
            <strong>Abogado:</strong> {{ $abogados->firstWhere('id', $abogado_id)->name ?? 'Sin asignar' }}
        </div>
    @endif

        @if($puedeVerNotas)
        <div class="col-span-12 sm:col-span-4">
            <label class="form-label">Estado atención</label>
            <select wire:model.defer="estado_atencion" class="form-select">
                <option value="pendiente">Pendiente</option>
                <option value="atendida">Atendida</option>
            </select>
        </div>

        {{-- HOJA DE TRABAJO: solo el abogado asignado (y admin) --}}
        <div class="col-span-12">
            <label class="form-label">Notas de la consulta (hoja de trabajo)</label>
            <div wire:ignore class="document-editor" x-data="{}"
                x-on:set-consulta-editor-content.window="if(window.consultaEditorInstance){ window.consultaEditorInstance.root.innerHTML = $event.detail.content || ''; }">
                <div id="toolbar-consulta-container"></div>
                <div class="editable-container">
                    <div id="editor-consulta-hoja"></div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary mt-2"
                onclick="if(typeof loadConsultaQuillEditor==='function') loadConsultaQuillEditor();">
                <i class="fas fa-pen"></i> Activar editor
            </button>
        </div>
        @endif
    </div>

    <div class="text-right mt-5">
        <button wire:click.prevent="CloseModal" class="btn btn-outline-secondary mr-1">Cancelar</button>
        @if($puedeAdministrar)
        <button wire:click.prevent="Store" class="btn btn-primary">Guardar datos</button>
        @endif
        @if($puedeVerNotas)
        <button wire:click.prevent="guardarNotas" class="btn btn-success text-white">Guardar notas</button>
        @endif
    </div>
</div>

<script>
    function loadConsultaQuillEditor() {
        if (typeof Quill !== 'undefined') { startConsultaWordEditor(); return; }
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
        script.addEventListener('load', () => { startConsultaWordEditor(); });
    }
    function startConsultaWordEditor() {
        const editorDom = document.querySelector('#editor-consulta-hoja');
        if (!editorDom) return;
        if (editorDom.classList.contains('ql-container')) return;
        var quill = new Quill('#editor-consulta-hoja', {
            theme: 'snow',
            placeholder: 'Redacte los pormenores de la consulta aquí...',
            modules: { toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['clean']
            ]}
        });
        const toolbarContainer = document.querySelector('#toolbar-consulta-container');
        const quillToolbar = document.querySelector('.editable-container .ql-toolbar');
        if (toolbarContainer && quillToolbar) {
            toolbarContainer.appendChild(quillToolbar);
        }
        window.consultaEditorInstance = quill;
        @this.set('notas', quill.root.innerHTML);
        quill.on('text-change', function() { @this.set('notas', quill.root.innerHTML); });
    }
    setTimeout(() => { loadConsultaQuillEditor(); }, 100);
</script>

<style>
    .document-editor { border: 1px solid #cbd5e1; background: white; display: flex; flex-direction: column; border-radius: 0.5rem; overflow: hidden; height: 600px; }
    .editable-container { flex-grow: 1; overflow-y: auto; background: #f1f5f9; padding: 40px 10px; }
    #editor-consulta-hoja { width: 21cm; min-height: 20cm; margin: 0 auto; padding: 2.5cm; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); color: black; font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; }
    .ql-container.ql-snow { border: none !important; }
    .ql-editor { padding: 0 !important; min-height: 100%; }
    #toolbar-consulta-container { background: #f8fafc !important; border-bottom: 1px solid #cbd5e1; }
    .ql-toolbar.ql-snow { border: none !important; padding: 10px !important; }
</style>
