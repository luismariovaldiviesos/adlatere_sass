@if($showQuickAbogado)
<div class="fixed top-0 left-0 w-full h-full flex items-center justify-center"
     style="z-index: 100000; background-color: rgba(0,0,0,0.65);"
     wire:click.self="closeQuickAbogado">
    <div onclick="event.stopPropagation()" class="bg-white rounded-xl shadow-2xl border-2 border-slate-300"
         style="width: 94%; max-width: 760px; max-height: 92vh; overflow-y: auto;">
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50 rounded-t-xl">
            <div>
                <h2 class="text-xl font-bold text-slate-800"><i class="fas fa-user-tie mr-2 text-blue-700"></i> Nuevo abogado</h2>
                <p class="text-xs text-slate-500 mt-1">Se creará con perfil Abogado y se asignará a este juicio.</p>
            </div>
            <button type="button" wire:click="closeQuickAbogado" class="text-slate-500 hover:text-red-600 text-3xl leading-none">&times;</button>
        </div>

        <div class="p-6 grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Nombre completo</label>
                <input wire:model.defer="qa_name" id="qa_name" type="text" class="form-control" maxlength="50">
                @error('qa_name') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Cédula / RUC</label>
                <input wire:model.defer="qa_ci" id="qa_ci" type="text" class="form-control" maxlength="13">
                @error('qa_ci') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Correo electrónico</label>
                <input wire:model.defer="qa_email" id="qa_email" type="email" class="form-control" maxlength="255">
                @error('qa_email') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Teléfono</label>
                <input wire:model.defer="qa_phone" id="qa_phone" type="text" class="form-control" maxlength="20">
                @error('qa_phone') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Contraseña temporal</label>
                <input wire:model.defer="qa_password" id="qa_password" type="password" class="form-control" autocomplete="new-password">
                @error('qa_password') <span class="text-theme-6 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-12 sm:col-span-6">
                <label class="form-label">Confirmar contraseña</label>
                <input wire:model.defer="qa_password_confirmation" id="qa_password_confirmation" type="password" class="form-control" autocomplete="new-password">
            </div>

            <div class="col-span-12">
                <p class="text-xs text-slate-500">
                    El abogado podrá completar especialidades, certificado de firma y demás datos desde Usuarios.
                </p>
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-slate-50 flex justify-end gap-2 rounded-b-xl">
            <button type="button" wire:click="closeQuickAbogado" class="btn btn-outline-secondary px-4">Cancelar</button>
            <button type="button" wire:click="saveQuickAbogado" wire:loading.attr="disabled" wire:target="saveQuickAbogado"
                    class="btn btn-primary px-5">
                <span wire:loading.remove wire:target="saveQuickAbogado"><i class="fas fa-save mr-2"></i>Crear y asignar</span>
                <span wire:loading wire:target="saveQuickAbogado">Guardando...</span>
            </button>
        </div>
    </div>
</div>
@endif
