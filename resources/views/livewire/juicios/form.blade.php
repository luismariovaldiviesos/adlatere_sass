{{-- FORM con pestañas (sin datos). Lógica en Livewire\Alumno, métodos usan dd(). --}}
<div x-data="{ tab: @entangle('tab') }" class="content space-y-6">

    {{-- BARRA DE PESTAÑAS --}}
    <div class="intro-y box p-6">
        <div class="flex flex-wrap gap-3">
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='juicio' }"  @click="tab='juicio'">Juicio</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='sujetos' }"   @click="tab='sujetos'">Sujetos Procesales</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='lesiones' }"@click="tab='lesiones'">Lesiones</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='rep' }"     @click="tab='rep'">Representante</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='matri' }"   @click="tab='matri'">Matrícula</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='fin' }"     @click="tab='fin'">Finanzas</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='eval' }" @click="tab='eval'">Evaluaciones</button>

        </div>
    </div>

    {{-- GRID PRINCIPAL --}}
    <div class="grid grid-cols-12 gap-8 items-start">
        {{-- IZQUIERDA: CONTENIDO DE LAS PESTAÑAS (más ancho) --}}
        <div class="col-span-12 xl:col-span-8 2xl:col-span-9 space-y-8">
            {{-- juicio --}}
            <div class="intro-y box" x-show="tab==='juicio'" x-cloak>
                @include('livewire.juicios.tabs.juicio')
            </div>

             {{-- sujetos prpcesales --}}
            <div class="intro-y box" x-show="tab==='sujetos'" x-cloak>
                @include('livewire.juicios.tabs.sujetos')
            </div>

            {{-- lesiones --}}
            <div class="intro-y box" x-show="tab==='lesiones'" x-cloak>
                @include('livewire.juicios.tabs.lesiones')
            </div>

          

           

            {{-- REPRESENTANTE --}}
            <div class="intro-y box" x-show="tab==='rep'" x-cloak>
                @include('livewire.juicios.tabs.representante')
            </div>

            {{-- MATRÍCULA --}}
            <div class="intro-y box" x-show="tab==='matri'" x-cloak>
                @include('livewire.juicios.tabs.matricula')
            </div>

            {{-- FINANZAS --}}
            <div class="intro-y box" x-show="tab==='fin'" x-cloak>
                @include('livewire.juicios.tabs.finanzas')
            </div>
            {{-- EVALUACIONES --}}
            <div class="intro-y box" x-show="tab==='eval'" x-cloak>
                @include('livewire.juicios.tabs.evaluaciones')
            </div>

        </div>

        {{-- DERECHA: SOLO FOTO + RESUMEN (sin input de foto) --}}
        <aside class="col-span-12 xl:col-span-4 2xl:col-span-3">
            @include('livewire.juicios.tabs.sidebar')
        </aside>

            {{-- =========================================================================
                                MODAL DE CREACIÓN RÁPIDA 
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
