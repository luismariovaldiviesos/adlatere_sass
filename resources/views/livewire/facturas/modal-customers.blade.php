<div wire:ignore.self id="modalCustomer" class="modal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header flex justify-between items-center">
                <h2 class="font-medium text-base mr-auto">
                    <b class="text-theme-1">{{ $showCreateCustomer ? 'Nuevo Cliente' : 'Elegir Cliente' }}</b>
                </h2>
                @if(!$showCreateCustomer)
                <button wire:click="showCreateCustomer" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> NUEVO CLIENTE
                </button>
                @else
                <button wire:click="hideCreateCustomer" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> VOLVER A BUSCAR
                </button>
                @endif
            </div>

            <div class="modal-body grid gap-4">
                @if(!$showCreateCustomer)
                <div wire:key="search-view-saas" class="row">
                    <div class="col-sm-12">
                        <div class="p-5" id="striped-rows-table">
                            <div class="preview">
                                <div class="overflow-x-auto">

                                    <div class="input-group">
                                        <div id="input-group-3" class="input-group-text"><i class="fas fa-search"></i></div>
                                        <input wire:model.debounce.500ms="searchCustomer" id="customer-search" type="text" class="form-control form-control-lg kioskboard" placeholder="Buscar cliente" >
                                    </div>


                                    <table class="table mt-2">
                                        <thead>
                                            <tr class="text-theme-6">
                                                <th class="border-b-2 dark:border-dark-5 whitespace-nowrap font-bold" width="80%">CLIENTE</th>
                                                <th class="border-b-2 dark:border-dark-5 whitespace-nowrap font-bold text-center">ACCION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customers as $customer)
                                            <tr class="dark:bg-dark-1 text-lg {{$loop->index % 2 > 0 ? 'bg-gray-200' : ''}}">
                                                <td class="border-b dark:border-dark-5 ">
                                                    {{$customer->businame}} <br>
                                                    <small class="text-gray-500">{{ $customer->valueidenti }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click.prevent="selectCustomer({{$customer->id}}, '{{$customer->businame}}')" class="btn btn-outline-primary btn-sm">Seleccionar</button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-5">
                                                    <p>NO SE ENCONTRARON CLIENTES</p>
                                                    <button wire:click="showCreateCustomer" class="btn btn-primary mt-2">
                                                        CREAR NUEVO CLIENTE
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @else
                {{-- FORMULARIO DE CREACIÓN RÁPIDA --}}
                <div wire:key="create-view-saas" class="grid grid-cols-12 gap-4 p-5">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Tipo Identificación</label>
                        <select wire:model="q_typeidenti" class="form-select">
                            <option value="cedula">Cédula</option>
                            <option value="ruc">RUC</option>
                            <option value="pasaporte">Pasaporte</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Identificación</label>
                        <input wire:model="q_valueidenti" type="text" class="form-control" placeholder="Ej: 0102030405">
                        @error('q_valueidenti') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Nombre / Razón Social</label>
                        <input wire:model="q_businame" type="text" class="form-control" placeholder="Ej: Juan Perez">
                        @error('q_businame') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Teléfono</label>
                        <input wire:model="q_phone" type="text" class="form-control" placeholder="Ej: 0987654321">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Email</label>
                        <input wire:model="q_email" type="email" class="form-control" placeholder="Ej: cliente@gmail.com">
                        @error('q_email') <span class="text-theme-6">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Dirección</label>
                        <textarea wire:model="q_address" class="form-control" rows="2" placeholder="Ej: Av. Las Americas 1-23"></textarea>
                    </div>
                    
                    <div class="col-span-12 text-center mt-5">
                        <button wire:click="saveQuickCustomer" class="btn btn-primary w-full py-3 font-bold">
                            <i class="fas fa-save mr-2"></i> GUARDAR Y SELECCIONAR
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <div class="modal-footer text-right">
                <button onclick="closeModalCustomer()" class="btn btn-outline-secondary mr-5">Cerrar Ventana</button>
            </div>

        </div>
    </div>
</div>
