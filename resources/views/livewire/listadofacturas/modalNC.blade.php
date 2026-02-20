<div id="modalNC" class="modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Emitir Nota de Crédito</h5>
            </div>
            <div class="modal-body">
                <div class="p-5 text-center">
                    <i data-feather="alert-circle" class="w-16 h-16 text-theme-12 mx-auto mt-3"></i>
                    <div class="text-3xl mt-5">Confirmación</div>
                    <div class="text-gray-600 mt-2">
                        Se emitirá una Nota de Crédito por el valor TOTAL de la factura.
                        <br>Esta acción firmará, autorizará y enviará el documento al SRI.
                    </div>
                </div>

                <div class="mt-3">
                    <label for="motivo" class="form-label">Motivo de la Modificación (Requerido por SRI)</label>
                    <textarea id="motivo" wire:model.defer="motivoNC" class="form-control" rows="3" placeholder="Ej: Devolución total de mercadería"></textarea>
                    @error('motivoNC') <span class="text-theme-6">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="modal-footer text-center">
                <button type="button" onclick="closeModalNC()" class="btn btn-outline-secondary w-24 mr-1">Cancelar</button>
                <button type="button" wire:click="emitirNC" class="btn btn-warning w-24">Emitir NC</button>
            </div>
        </div>
    </div>
</div>


