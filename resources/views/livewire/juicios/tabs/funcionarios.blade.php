<div class="p-8 space-y-6">
    {{-- Formulario para NUEVA evaluación --}}
    <div class="intro-y box p-6">
        <div class="grid grid-cols-12 gap-6 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="form-label text-base">Fecha</label>
                <input type="date" class="form-control h-12 text-lg">
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="form-label text-base">Tipo</label>
                <select class="form-select h-12 text-lg">
                    <option value="inicial">Inicial</option>
                    <option value="seguimiento" selected>Seguimiento</option>
                </select>
            </div>
            <div class="col-span-12 md:col-span-6">
                <label class="form-label text-base">Observaciones</label>
                <input type="text" class="form-control h-12 text-lg" placeholder="Comentarios">
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-base font-medium mb-3">Métricas</h4>
            {{-- Repeater simple de métricas (placeholder) --}}
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-4">
                    <label class="form-label">Métrica</label>
                    <select class="form-select">
                        <option>Velocidad 20m</option>
                        <option>Salto vertical</option>
                        <option>IMC</option>
                        {{-- DATA: @foreach($metrics as $m) ... @endforeach --}}
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">Valor</label>
                    <input type="number" step="0.001" class="form-control" placeholder="0.000">
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">Unidad</label>
                    <input type="text" class="form-control" placeholder="s / cm / kg / %">
                </div>
                <div class="col-span-12 md:col-span-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-full">Agregar otra</button>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button class="btn btn-primary text-lg px-8 py-2.5" wire:click="saveEvaluacion">
                    Guardar evaluación
                </button>
            </div>
        </div>
    </div>

    {{-- Historial (para listar y luego graficar) --}}
    <div class="intro-y box p-6">
        <h4 class="text-base font-medium mb-4">Historial de evaluaciones</h4>
        <div class="overflow-x-auto">
            <table class="table text-base">
                <thead><tr><th>Fecha</th><th>Tipo</th><th>Métricas registradas</th><th>Obs.</th><th>Acciones</th></tr></thead>
                <tbody>
                    <tr>
                        <td class="text-slate-400">--</td>
                        <td class="text-slate-400">--</td>
                        <td class="text-slate-400">--</td>
                        <td class="text-slate-400">--</td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn btn-outline-primary btn-sm">Ver</button>
                                <button class="btn btn-outline-danger btn-sm" wire:click="deleteEvaluacion(0)">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    {{-- DATA: @foreach($evaluations as $ev)... @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
