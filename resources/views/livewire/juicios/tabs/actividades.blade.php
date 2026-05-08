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
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Tipo Actividad</label>
            <select wire:model="tipo_actividad_id" class="form-select h-12 text-lg">
                <option value="">Seleccione…</option>
                @foreach($tipos_actividades as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>
            @error('tipo_actividad_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        {{-- ORIGEN --}}
        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Origen Actividad</label>
            <select wire:model="origen" class="form-select h-12 text-lg">
                <option value="Interno">Interno (Usar Plantilla)</option>
                <option value="Externo">Externo (Cargar Documento)</option>
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
        <div class="col-span-12 mt-4" x-show="$wire.origen == 'Interno'">
            <label class="form-label text-base font-bold">Documento de la Actividad</label>
            <div wire:ignore class="document-editor-wrapper">
                {{-- Barra de herramientas fija --}}
                <div id="toolbar-actividad"></div>
                
                {{-- Hoja de trabajo --}}
                <div class="editable-scroll-container">
                    <div id="editor-actividad">
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
            <button type="button" class="btn btn-primary text-lg px-8 py-2.5" wire:click.prevent="Store">
                Registrar Actuación y Actualizar Juicio
            </button>
        </div>
    </div>
</div>

{{-- Scripts para manejar el editor Decoupled que configuramos antes --}}
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/decoupled-document/ckeditor.js" onload="initActividadEditor()"></script>

<script>
    function initActividadEditor() {
        const dom = document.querySelector('#editor-actividad');
        if (!dom) return;

        DecoupledEditor
            .create(dom)
            .then(editor => {
                const toolbar = document.querySelector('#toolbar-actividad');
                toolbar.appendChild(editor.ui.view.toolbar.element);
                window.actividadEditor = editor;

                editor.model.document.on('change:data', () => {
                    @this.set('contenido', editor.getData());
                });

                // Este evento es clave para cargar la plantilla automáticamente
                window.addEventListener('set-editor-content', event => {
                    editor.setData(event.detail.content);
                });
            });
    }

    document.addEventListener('livewire:load', () => {
        if (typeof DecoupledEditor !== 'undefined') initActividadEditor();
    });
</script>

<style>
    .document-editor-wrapper { border: 1px solid #cbd5e1; background: white; border-radius: 0.5rem; overflow: hidden; }
    #toolbar-actividad { background: #f8fafc; border-bottom: 1px solid #cbd5e1; }
    .editable-scroll-container { background: #f1f5f9; padding: 20px; max-height: 600px; overflow-y: auto; display: flex; justify-content: center; }
    #editor-actividad { width: 21cm; min-height: 29.7cm; padding: 2cm; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); outline: none; cursor: text !important; }
</style>