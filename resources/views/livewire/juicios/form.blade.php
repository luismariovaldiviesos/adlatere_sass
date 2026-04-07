{{-- FORM con pestañas (sin datos). Lógica en Livewire\Alumno, métodos usan dd(). --}}
<div x-data="{ tab: @entangle('tab') }" class="content space-y-6">

    {{-- BARRA DE PESTAÑAS --}}
    <div class="intro-y box p-6">
        <div class="flex flex-wrap gap-3">
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='perfil' }"  @click="tab='perfil'">Perfil</button>
            <button class="btn px-6 py-2" :class="{ 'btn-primary': tab==='ficha' }"   @click="tab='ficha'">Ficha deportiva</button>
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
            {{-- PERFIL --}}
            <div class="intro-y box" x-show="tab==='perfil'" x-cloak>
                @include('livewire.juicios.tabs.perfil')
            </div>

            {{-- FICHA DEPORTIVA --}}
            <div class="intro-y box" x-show="tab==='ficha'" x-cloak>
                @include('livewire.juicios.tabs.ficha')
            </div>

            {{-- LESIONES --}}
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
    </div>
</div>
