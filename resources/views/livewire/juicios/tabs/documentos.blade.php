<div class="p-8 grid grid-cols-12 gap-6">
    {{-- categoria (select con id), fecha matricula, estado (activa por default), costo mensual, observaciones --}}
    <div class="col-span-12 md:col-span-6">
        <label class="form-label text-base">Categoría</label>
        <select class="form-select h-12 text-lg" wire:model="categoria_id">
            <option value="">Seleccione…</option>
             DATA: @foreach($asuntos as $as) <option  value="{{ $as->id }}">{{ $as->nombre }}</option> @endforeach 
        </select>
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">Fecha de matrícula</label>
        <input type="date" class="form-control h-12 text-lg" wire:model="fecha_matricula">
    </div>

    <div class="col-span-12 md:col-span-3">
        <label class="form-label text-base">Estado</label>
        <select class="form-select h-12 text-lg" wire:model="mat_estado">
            <option selected>Activa</option>
            <option>Inactiva</option>
            <option>Suspendida</option>
            <option>Egresada</option>
        </select>
    </div>
    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-base">Valor matrícula </label>
        <input type="number" step="0.01" class="form-control h-12 text-lg" placeholder="0.00" wire:model="costo_matricula"    wire:change="updatedCategoriaId($categoria_id)">
    </div>

    <div class="col-span-12 md:col-span-4">
        <label class="form-label text-base">Costo mensual</label>
        <input type="number" step="0.01" class="form-control h-12 text-lg" placeholder="0.00" wire:model="costo_mensual"    wire:change="updatedCategoriaId($categoria_id)">
    </div>

    <div class="col-span-12">
        <label class="form-label text-base">Observaciones</label>
        <textarea class="form-control text-lg" rows="3" placeholder="Comentarios u observaciones" wire:model="mat_observaciones"></textarea>
    </div>

    <div class="col-span-12 flex justify-end">
        <button class="btn btn-primary text-lg px-8 py-2.5" wire:click="saveMatricula">
            Guardar matrícula
        </button>
    </div>
</div>
