<div class="col-span-12 sm:col-span-4">
    <label class="form-label">Estado civil</label>
    <select wire:model="{{ $prefix }}estado_civil" id="{{ $prefix }}estado_civil" class="form-control form-control-lg">
        <option value="">Seleccione...</option>
        <option value="Soltero/a">Soltero/a</option>
        <option value="Casado/a">Casado/a</option>
        <option value="Divorciado/a">Divorciado/a</option>
        <option value="Viudo/a">Viudo/a</option>
        <option value="Unión de hecho">Unión de hecho</option>
        <option value="Otro">Otro</option>
    </select>
    @error($prefix . 'estado_civil')
        <x-alert msg="{{ $message }}" />
    @enderror
</div>

<div class="col-span-12 sm:col-span-4">
    <label class="form-label">Profesión / Ocupación</label>
    <input wire:model="{{ $prefix }}profesion_ocupacion" id="{{ $prefix }}profesion_ocupacion" type="text"
        class="form-control form-control-lg" maxlength="150" placeholder="Ej: Abogado, Comerciante, Estudiante">
    @error($prefix . 'profesion_ocupacion')
        <x-alert msg="{{ $message }}" />
    @enderror
</div>

<div class="col-span-12 sm:col-span-4">
    <label class="form-label">Cantón</label>
    <select wire:model="{{ $prefix }}canton_id" id="{{ $prefix }}canton_id" class="form-control form-control-lg">
        <option value="">Seleccione...</option>
        @foreach($cantones as $canton)
            <option value="{{ $canton->id }}">{{ $canton->nombre }}</option>
        @endforeach
    </select>
    @error($prefix . 'canton_id')
        <x-alert msg="{{ $message }}" />
    @enderror
</div>

<div class="col-span-12">
    <label class="form-label">Notas</label>
    <input wire:model="{{ $prefix }}notes" id="{{ $prefix }}notes" type="text"
        class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
    @error($prefix . 'notes')
        <x-alert msg="{{ $message }}" />
    @enderror
</div>
