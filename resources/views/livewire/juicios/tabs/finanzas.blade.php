<div class="p-8 space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium">Finanzas</h3>
        <div class="flex gap-2">
            <button class="btn btn-outline-primary px-4 py-2 text-base" wire:click="registrarPago">Registrar pago</button>
            <button class="btn btn-outline-secondary px-4 py-2 text-base" wire:click="generarCuotaMes">Generar cuota</button>
        </div>
    </div>

    {{-- Cuotas (simulado) --}}
    <div class="overflow-x-auto">
        <table class="table text-base">
            <thead><tr><th>Periodo</th><th>Debido</th><th>Aplicado</th><th>Saldo</th><th>Estado</th></tr></thead>
            <tbody><tr><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td></tr></tbody>
        </table>
    </div>

    {{-- Pagos (opcional) --}}
</div>
