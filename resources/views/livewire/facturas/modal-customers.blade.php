<div wire:ignore.self id="modalCustomer" class="modal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header flex justify-between items-center">
                <h2 class="font-medium text-base mr-auto">
                    <b class="text-theme-1">{{ $showCreateCustomer ? 'Nuevo Cliente' : 'Elegir Cliente' }}</b>
                </h2>
                @if(!$showCreateCustomer)
                <button type="button" wire:click="showCreateCustomer" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> NUEVO CLIENTE
                </button>
                @else
                <button type="button" wire:click="hideCreateCustomer" class="btn btn-secondary btn-sm">
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
                                                    <button type="button" wire:click.prevent="selectCustomer({{$customer->id}}, '{{$customer->businame}}')" class="btn btn-outline-primary btn-sm">Seleccionar</button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-5">
                                                    <p>NO SE ENCONTRARON CLIENTES</p>
                                                    <button type="button" wire:click="showCreateCustomer" class="btn btn-primary mt-2">
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
                <div wire:key="create-view-saas" class="p-5">
                    <x-customer-fields prefix="q_" :cantones="$q_cantones" context="billing" />

                    <div class="text-center mt-5">
                        <button type="button" wire:click="saveQuickCustomer" class="btn btn-primary w-full py-3 font-bold">
                            <i class="fas fa-save mr-2"></i> GUARDAR Y SELECCIONAR
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <div class="modal-footer text-right">
                <button type="button" onclick="closeModalCustomer()" class="btn btn-outline-secondary mr-5">Cerrar Ventana</button>
            </div>

        </div>
    </div>
</div>
