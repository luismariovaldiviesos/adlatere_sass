@if($showQuickFuncionario)
<div class="fixed top-0 left-0 w-full h-full flex items-center justify-center"
     style="z-index: 100000; background-color: rgba(0,0,0,0.65);"
     wire:click.self="closeQuickFuncionario">
    <div onclick="event.stopPropagation()" class="bg-white rounded-xl shadow-2xl border-2 border-slate-300"
         style="width: 94%; max-width: 760px; max-height: 92vh; overflow-y: auto;">
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50 rounded-t-xl">
            <div>
                <h2 class="text-xl font-bold text-slate-800"><i class="fas fa-gavel mr-2 text-blue-700"></i> Nuevo funcionario judicial</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Unidad actual: <strong>{{ $qf_unidad_nombre ?: 'Sin unidad seleccionada' }}</strong>
                </p>
            </div>
            <button type="button" wire:click="closeQuickFuncionario" class="text-slate-500 hover:text-red-600 text-3xl leading-none">&times;</button>
        </div>

        <div class="p-6 grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Nombre completo</label>
                <input wire:model.defer="qf_nombre" id="qf_nombre" type="text" class="form-control" maxlength="50">
                @error('qf_nombre') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Cargo</label>
                <select wire:model="qf_cargo" id="qf_cargo" class="form-select">
                    <option value="">Seleccione...</option>
                    <option value="Juez">Juez</option>
                    <option value="Secretario">Secretario</option>
                    <option value="Ayudante">Ayudante</option>
                    <option value="Citador">Citador</option>
                    <option value="Otro">Otro</option>
                </select>
                @error('qf_cargo') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Teléfono</label>
                <input wire:model.defer="qf_telefono" id="qf_telefono" type="text" class="form-control" maxlength="20">
                @error('qf_telefono') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Correo electrónico</label>
                <input wire:model.defer="qf_email" id="qf_email" type="email" class="form-control" maxlength="255">
                @error('qf_email') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12">
                <label class="form-label">Rol en este juicio</label>
                <select wire:model.defer="qf_rol_en_juicio" class="form-select">
                    <option value="">Seleccione...</option>
                    <option value="Juez Ponente">Juez Ponente</option>
                    <option value="Secretario">Secretario</option>
                    <option value="Fiscal de Turno">Fiscal de Turno</option>
                    <option value="Ayudante Judicial">Ayudante Judicial</option>
                    <option value="Citador">Citador</option>
                </select>
                @error('qf_rol_en_juicio') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12">
                <p class="text-xs text-slate-500">
                    Se registrará en el catálogo de funcionarios y se asignará a la unidad judicial del juicio.
                </p>
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-slate-50 flex justify-end gap-2 rounded-b-xl">
            <button type="button" wire:click="closeQuickFuncionario" class="btn btn-outline-secondary px-4">Cancelar</button>
            <button type="button" wire:click="saveQuickFuncionario" wire:loading.attr="disabled" wire:target="saveQuickFuncionario"
                    class="btn btn-primary px-5">
                <span wire:loading.remove wire:target="saveQuickFuncionario"><i class="fas fa-save mr-2"></i>Crear y asignar</span>
                <span wire:loading wire:target="saveQuickFuncionario">Guardando...</span>
            </button>
        </div>
    </div>
</div>
@endif
