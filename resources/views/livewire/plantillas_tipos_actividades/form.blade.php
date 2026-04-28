<div class="intro-y col-span-12">
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">
                {{ $componentName }} | <span class="font-normal">{{ $action }}</span>
            </h2>
        </div>

        <div class="p-5 bg-slate-100 dark:bg-dark-1">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 md:col-span-8">
                    <label class="form-label font-bold">Nombre de la Plantilla</label>
                    <input wire:model.defer='nombre' type="text" class="form-control form-control-lg kioskboard" maxlength="250">
                    @error('nombre') <x-alert msg="{{ $message }}" /> @enderror
                </div>

                <div class="col-span-12 md:col-span-4">
                    <label class="form-label font-bold">Tipo de Actividad</label>
                    <select wire:model.defer='tipo_actividad_id' class="form-select form-control-lg">
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tipo_actividad_id') <x-alert msg="{{ $message }}" /> @enderror
                </div>

                <div class="col-span-12 mt-5">
                    <div wire:ignore class="document-editor">
                        <div id="toolbar-container" class="border-b border-gray-300"></div>
                        
                        <div class="editable-container">
                            <div id="editor-hoja">
                                {!! $contenido !!}
                            </div>
                        </div>
                    </div>
                    @error('contenido') <x-alert msg="{{ $message }}" /> @enderror
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <x-back wire:click="resetUI" />
                <x-save wire:click="Store" />
            </div>
        </div>
    </div>

    <script>
        function loadQuillEditor() {
            if (typeof Quill !== 'undefined') {
                startWordEditor();
                return;
            }

            // Cargar CSS
            if (!document.querySelector('#quill-css')) {
                let link = document.createElement('link');
                link.id = 'quill-css';
                link.rel = 'stylesheet';
                link.href = 'https://cdn.quilljs.com/1.3.6/quill.snow.css';
                document.head.appendChild(link);
            }

            // Cargar JS
            let script = document.querySelector('#quill-script');
            if (!script) {
                script = document.createElement('script');
                script.id = 'quill-script';
                script.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
                document.head.appendChild(script);
            }

            script.addEventListener('load', () => {
                startWordEditor();
            });
        }

        function startWordEditor() {
            const editorDom = document.querySelector('#editor-hoja');
            if (!editorDom) return;

            if (editorDom.classList.contains('ql-container')) return;

            var quill = new Quill('#editor-hoja', {
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

            // Mover la barra de herramientas al contenedor superior
            const toolbarContainer = document.querySelector('#toolbar-container');
            const quillToolbar = document.querySelector('.ql-toolbar');
            if (toolbarContainer && quillToolbar) {
                toolbarContainer.appendChild(quillToolbar);
            }

            window.editorInstance = quill;

            // Sincronizar datos con Livewire
            quill.on('text-change', function() {
                @this.set('contenido', quill.root.innerHTML);
            });

            // Escuchar evento para cargar contenido al editar (solo si no se ha registrado)
            if (!window.hasSetEditorContentListener) {
                window.addEventListener('set-editor-content', event => {
                    if (window.editorInstance) {
                        window.editorInstance.root.innerHTML = event.detail.content;
                    }
                });
                window.hasSetEditorContentListener = true;
            }
        }

        // Ejecutar inmediatamente cuando Livewire inyecta esta vista
        setTimeout(() => {
            loadQuillEditor();
        }, 100);
    </script>

    <style>
        /* Estructura del editor tipo Word */
        .document-editor {
            border: 1px solid #cbd5e1;
            background: white;
            display: flex;
            flex-direction: column;
            border-radius: 0.5rem;
            overflow: hidden;
            height: 800px; /* Altura fija para que la barra de herramientas no se pierda */
        }

        /* Área de scroll (Fondo grisáceo) */
        .editable-container {
            flex-grow: 1;
            overflow-y: auto;
            background: #f1f5f9;
            padding: 40px 10px;
        }

        /* La Hoja A4 blanca */
        #editor-hoja {
            width: 21cm;
            min-height: 29.7cm; /* Tamaño de una hoja A4 inicial */
            margin: 0 auto;
            padding: 2.5cm; /* Márgenes legales estándar */
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            color: black;
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
        }

        /* Quitar bordes por defecto de quill en la hoja */
        .ql-container.ql-snow {
            border: none !important;
        }
        
        .ql-editor {
            padding: 0 !important;
            min-height: 100%;
        }

        /* Ajustes para la barra de herramientas fija */
        #toolbar-container {
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1;
        }
        
        .ql-toolbar.ql-snow {
            border: none !important;
            padding: 10px !important;
        }
    </style>
</div>