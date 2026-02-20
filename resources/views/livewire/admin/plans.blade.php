<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Gestión de Planes Globales
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button wire:click="openModal" class="btn btn-primary shadow-md mr-2">Agregar Plan</button>
            <div class="hidden md:block mx-auto text-gray-600"></div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <input wire:model.debounce.300ms="search" type="text" class="form-control w-56 box pr-10 placeholder-theme-13" placeholder="Buscar...">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i>
                </div>
            </div>
        </div>

        <!-- Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">NOMBRE</th>
                        <th class="text-center whitespace-nowrap">DOCS/MES</th>
                        <th class="text-center whitespace-nowrap">PRECIO</th>
                        <th class="text-center whitespace-nowrap">DESCRIPCIÓN</th>
                        <th class="text-center whitespace-nowrap">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr class="intro-x">
                        <td>
                            <a href="#" class="font-medium whitespace-nowrap">{{ $plan->name }}</a>
                        </td>
                        <td class="text-center font-medium">
                            {{ $plan->invoice_limit ?? 'Ilimitado' }}
                        </td>
                        <td class="text-center">
                            <span class="font-bold text-gray-800">${{ number_format($plan->price, 2) }}</span>
                        </td>
                        <td class="text-center text-gray-600">
                            {{ Str::limit($plan->description, 50) }}
                        </td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <button wire:click="edit({{ $plan->id }})" class="flex items-center mr-3 text-theme-1"> 
                                    <i data-feather="edit" class="w-4 h-4 mr-1"></i> Editar 
                                </button>
                                <button wire:click="destroy({{ $plan->id }})" class="flex items-center text-theme-6" onclick="confirm('¿Seguro que deseas eliminar este plan?') || event.stopImmediatePropagation()"> 
                                    <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> Eliminar 
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">No hay planes registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
             {{ $plans->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($modalOpen)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.5);">
        <div class="intro-y box w-11/12 md:w-1/2 lg:w-1/3 p-5 shadow-xl">
             <div class="flex items-center border-b border-gray-200 pb-5 mb-5">
                 <h2 class="font-medium text-base mr-auto">
                     {{ $planId ? 'Editar Plan' : 'Crear Nuevo Plan' }}
                 </h2>
                 <button wire:click="closeModal" class="text-gray-600 hover:text-gray-800">
                    <i data-feather="x" class="w-6 h-6"></i>
                 </button>
             </div>

             <form wire:submit.prevent="save">
                <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">Nombre del Plan</label>
                        <input wire:model="name" type="text" class="form-control" placeholder="Ej: Emprendedor">
                        @error('name') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="col-span-12">
                        <label class="form-label">Límite de Documentos (Dejar vacío para ilimitado)</label>
                        <input wire:model="invoice_limit" type="number" class="form-control" placeholder="Ej: 30">
                        @error('invoice_limit') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-12">
                        <label class="form-label">Precio ($)</label>
                        <input wire:model="price" type="number" step="0.01" class="form-control" placeholder="0.00">
                        @error('price') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-12">
                        <label class="form-label">Descripción o ID Remoto</label>
                        <textarea wire:model="description" class="form-control" placeholder="Descripción breve..."></textarea>
                    </div>
                </div>

                <div class="text-right mt-5">
                    <button type="button" wire:click="closeModal" class="btn btn-outline-secondary w-20 mr-1">Cancelar</button>
                    <button type="submit" class="btn btn-primary w-24">Guardar</button>
                </div>
             </form>
         </div>
    </div>
    @endif
    
    <script>
        document.addEventListener('livewire:load', function () {
            feather.replace();
        });
        document.addEventListener('livewire:update', function () {
            feather.replace();
        });
    </script>
</div>
