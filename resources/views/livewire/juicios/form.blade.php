{{-- FORM con pestañas (sin datos). Lógica en Livewire\Alumno, métodos usan dd(). --}}
@php
    // Precarga única de colecciones de pestañas (loadMissing = 0 consultas si ya están).
    // La carga maestra trae lo mínimo; esto completa lo que muestran las pestañas.
    if (isset($juicio) && $juicio) {
        $juicio->loadMissing(['actividades.tipoActividad', 'actividades.documentos', 'audiencias', 'audiencias.documentos', 'documentos.tipoDocumento', 'documentos.estadoProcesal', 'documentos.vinculos.vinculable', 'funcionarios', 'abogados.especialidades', 'participantes']);
    }

@endphp
<div id="juicio-form" x-data="{ tab: @entangle('tab').defer }" class="content space-y-6">

    {{-- BARRA DE PESTAÑAS --}}
    <div class="intro-y box p-6">
        <div class="flex flex-wrap gap-3 items-center">
            <button type="button" wire:click="volverListado" wire:loading.attr="disabled" wire:target="volverListado" onclick="this.style.background='#fef3c7';setTimeout(function(){if(document.getElementById('juicio-form')){window.location.reload();}},2000)" title="Volver al listado de juicios" class="px-6 py-2 btn btn-outline-secondary">
                <span wire:loading.remove wire:target="volverListado"><i class="fas fa-arrow-left mr-1"></i> Volver</span>
                <span wire:loading wire:target="volverListado">Volviendo…</span>
            </button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='juicio' }"  @click="tab='juicio'">Juicio</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='sujetos' }"   @click="tab='sujetos'">Sujetos Procesales</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='actividades' }" @click="tab='actividades'; setTimeout(() => { if(typeof loadActividadQuillEditor === 'function') loadActividadQuillEditor(); }, 150)">Actividades</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='audiencias' }"     @click="tab='audiencias'">Audiencias</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='documentos' }"   @click="tab='documentos'">Documentos</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='funcionarios' }" @click="tab='funcionarios'">Funcionarios</button>
            <button type="button" class=" px-6 py-2" :class="{ 'btn-primary': tab==='abogados' }" @click="tab='abogados'">Abogados</button>

        </div>
    </div>

    @if(isset($faltantesBase) && count($faltantesBase))
    <div class="intro-y box p-5 border-l-4 border-theme-12 bg-yellow-50">
        <div class="flex items-start gap-3">
            <i class="fas fa-lock text-theme-12 text-xl mt-1"></i>
            <div>
                <h3 class="font-bold text-theme-12">Trámite bloqueado</h3>
                <p class="text-gray-700 mt-1">Complete la base del juicio antes de registrar actividades, audiencias, documentos o funcionarios. Falta: <strong>{{ implode(', ', $faltantesBase) }}</strong>.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- GRID PRINCIPAL --}}
    <div class="grid grid-cols-12 gap-8 items-start">
        {{-- IZQUIERDA: CONTENIDO DE LAS PESTAÑAS (más ancho) --}}
        <div class="col-span-12 xl:col-span-8 2xl:col-span-9 space-y-8">
            {{-- juicio --}}
            <div class="intro-y box" x-show="tab==='juicio'" x-cloak>
                @include('livewire.juicios.tabs.juicio')
            </div>

             {{-- sujetos procesales --}}
            <div class="intro-y box" x-show="tab==='sujetos'" x-cloak>
                @include('livewire.juicios.tabs.sujetos')
            </div>

            {{-- actividades --}}
            <div class="intro-y box" x-show="tab==='actividades'" x-cloak>
                @include('livewire.juicios.tabs.actividades')
            </div>

            {{-- audiencias --}}
            <div class="intro-y box" x-show="tab==='audiencias'" x-cloak>
                @include('livewire.juicios.tabs.audiencias')
            </div>

            {{-- documentos --}}
            <div class="intro-y box" x-show="tab==='documentos'" x-cloak>
                @include('livewire.juicios.tabs.documentos')
            </div>

            {{-- funcionarios --}}
            <div class="intro-y box" x-show="tab==='funcionarios'" x-cloak>
                @include('livewire.juicios.tabs.funcionarios')
            </div>
            {{-- abogados --}}
             <div class="intro-y box" x-show="tab==='abogados'" x-cloak>
                @include('livewire.juicios.tabs.abogados')
            </div>

        </div>

        {{-- DERECHA: SOLO FOTO + RESUMEN (sin input de foto) --}}
        <aside class="col-span-12 xl:col-span-4 2xl:col-span-3">
            @include('livewire.juicios.tabs.sidebar')
        </aside>

    {{-- MODAL DE DOCUMENTOS DEL CONTEXTO (ACTIVIDAD / AUDIENCIA / TAREA / CONSULTA) --}}
    @if($doc_modal)
    <div class="fixed top-0 left-0 w-full h-full flex items-center justify-center"
         style="z-index: 100000; background-color: rgba(0,0,0,0.65);"
         wire:click="cerrarDocumentosModal">
        <div onclick="event.stopPropagation()" class="bg-white rounded-xl shadow-2xl border-2 border-slate-300"
             style="width: 94%; max-width: 980px; max-height: 92vh; overflow-y: auto;">
            <div class="flex justify-between items-center px-6 py-4 sticky top-0 z-10"
                 style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                <div>
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-folder-open mr-2"></i> Documentos del contexto</h2>
                    <p class="text-blue-100 text-sm mt-0.5">{{ $doc_contexto_titulo ?: 'Expediente del juicio' }}</p>
                </div>
                <button type="button" wire:click="cerrarDocumentosModal" class="text-white hover:text-gray-200 text-3xl font-bold leading-none px-2" title="Cerrar">&times;</button>
            </div>

            <div class="p-6 space-y-6">
                @if($puedeTramitar ?? false)
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-200">
                    @include('livewire.juicios.tabs.documentos-form')
                </div>
                @endif

                <div>
                    <h3 class="font-bold text-slate-700 mb-3">Documentos ya vinculados ({{ $documentosContexto->count() }})</h3>
                    @if($documentosContexto->count())
                    <div class="space-y-2">
                        @foreach($documentosContexto as $doc)
                        <div class="flex items-center justify-between gap-3 p-3 border rounded-lg bg-white">
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $doc->nombre }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $doc->tipoDocumento->nombre ?? 'Sin tipo' }}
                                    · {{ $doc->created_at->format('d/m/Y H:i') }}
                                    · {{ number_format($doc->peso_kb / 1024, 2) }} MB
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a class="btn btn-sm btn-warning text-white" href="{{ route('tenant.media', ['path' => $doc->ruta_archivo]) }}" target="_blank">Ver</a>
                                @if($puedeTramitar ?? false)
                                <a class="btn btn-sm btn-danger text-white" href="javascript:;"
                                   onclick="confirm('¿Eliminar este documento?') || event.stopImmediatePropagation()"
                                   wire:click="destroyDocumento({{ $doc->id }})">Eliminar</a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-slate-500 italic">Todavía no hay documentos vinculados a este contexto.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

            {{-- =========================================================================
                                MODAL DE CREACI�"N R�?PIDA
       ========================================================================= --}}
    <div wire:ignore.self id="modalQuickCustomer" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Modal más ancho para mayor comodidad -->
            <div class="modal-content">
                <div class="modal-header bg-gray-100 border-b">
                    <h2 class="font-bold text-lg mr-auto text-gray-700">Registrar Nuevo Sujeto Procesal</h2>
                </div>

                <div class="modal-body grid grid-cols-12 gap-5 gap-y-4 p-6">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label font-bold">Tipo Identificación</label>
                        <select wire:model="q_typeidenti" class="form-select text-lg">
                            <option value="ci">Cédula</option>
                            <option value="ruc">RUC</option>
                            <option value="pasaporte">Pasaporte</option>
                        </select>
                    </div>

                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label font-bold">Identificación</label>
                        <input wire:model="q_valueidenti" type="text" class="form-control text-lg" placeholder="Ej: 0102030405">
                        @error('q_valueidenti') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-12">
                        <label class="form-label font-bold">Nombre / Razón Social</label>
                        <input wire:model="q_businame" type="text" class="form-control text-lg" placeholder="Ej: Juan Perez">
                        @error('q_businame') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label font-bold">Teléfono</label>
                        <input wire:model="q_phone" type="text" class="form-control text-lg" placeholder="099...">
                    </div>

                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label font-bold">Correo Electrónico <small>(Opcional)</small></label>
                        <input wire:model="q_email" type="email" class="form-control text-lg" placeholder="correo@ejemplo.com">
                        @error('q_email') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-footer text-right bg-gray-50 border-t">
                    <button type="button" data-dismiss="modal" class="btn btn-outline-secondary w-24 mr-2">Cancelar</button>
                    <button type="button" wire:click="saveQuickCustomer" class="btn btn-primary w-24 shadow-md">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- FIN MODAL --}}
    </div>

    <!-- SCRIPT PARA ABRIR Y CERRAR EL MODAL CON TAILWIND/TOM-SELECT -->
<script>
    // JS Nativo para forzar tu tema visual (Midone/Rubick/Tinker)
    function openModalQuickCustomer() {
        var modal = document.getElementById("modalQuickCustomer")
        modal.classList.add("overflow-y-auto", "show")
       modal.style.cssText = "margin-top: 0px; margin-left: 0px; z-index: 100000 !important;"
    }

    function closeModalQuickCustomer() {
        var modal = document.getElementById("modalQuickCustomer")
        modal.classList.remove("overflow-y-auto", "show")
        modal.style.cssText = ""
    }

    // Escuchando los llamados desde Livewire ($this->dispatchBrowserEvent...)
    window.addEventListener('open-modal-quick-customer', event => {
        openModalQuickCustomer();
    });

    window.addEventListener('close-modal-quick-customer', event => {
        closeModalQuickCustomer();
    });
</script>

</div>
