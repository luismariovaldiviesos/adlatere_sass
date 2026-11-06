@props(['prefix' => '', 'cantones' => [], 'context' => 'full'])

@php($billing = $context === 'billing')

<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-6">
        <label class="form-label">Nombre-Razón Social</label>
        <input wire:model="{{ $prefix }}businame" id="{{ $prefix }}businame" type="text"
            class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
        @error($prefix . 'businame')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12 sm:col-span-3">
        <label class="form-label">Tipo</label>
        <select wire:model="{{ $prefix }}typeidenti" id="{{ $prefix }}typeidenti"
            class="form-control form-control-lg border-start-0 kioskboard">
            <option value="">Seleccione...</option>
            <option value="ci">Cédula</option>
            <option value="ruc">RUC</option>
            <option value="06">Pasaporte</option>
        </select>
        @error($prefix . 'typeidenti')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12 sm:col-span-3">
        <label class="form-label">CI-RUC</label>
        <input wire:model="{{ $prefix }}valueidenti" id="{{ $prefix }}valueidenti" type="text"
            data-kioskboard-type="numpad" class="form-control form-control-lg border-start-0 kioskboard" maxlength="13">
        @error($prefix . 'valueidenti')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12">
        <label class="form-label">Dirección</label>
        <input wire:model="{{ $prefix }}address" id="{{ $prefix }}address" type="text"
            class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
        @error($prefix . 'address')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12 sm:col-span-6">
        <label class="form-label">Email</label>
        <input wire:model="{{ $prefix }}email" id="{{ $prefix }}email" type="text"
            class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
        @error($prefix . 'email')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    <div class="col-span-12 sm:col-span-6">
        <label class="form-label">Teléfono</label>
        <input wire:model="{{ $prefix }}phone" id="{{ $prefix }}phone" type="text"
            data-kioskboard-type="numpad" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
        @error($prefix . 'phone')
            <x-alert msg="{{ $message }}" />
        @enderror
    </div>

    @if($billing)
        <div class="col-span-12">
            <details class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                    Más datos (opcional): estado civil, profesión, cantón y notas
                </summary>
                <div class="grid grid-cols-12 gap-4 mt-3">
                    @include('components.customer-fields-advanced', ['prefix' => $prefix, 'cantones' => $cantones])
                </div>
            </details>
        </div>
    @else
        @include('components.customer-fields-advanced', ['prefix' => $prefix, 'cantones' => $cantones])
    @endif
</div>
