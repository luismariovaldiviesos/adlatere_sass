<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Administración de Tenants
        </h2>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5">
                    <div class="flex">
                        <i data-feather="users" class="report-box__icon text-theme-10"></i> 
                        <div class="ml-auto">
                            <div class="report-box__indicator bg-theme-9 tooltip cursor-pointer" title="Total Registrados"> 
                                {{ $records }} <i data-feather="chevron-up" class="w-4 h-4 ml-0.5"></i> 
                            </div>
                        </div>
                    </div>
                    <div class="text-3xl font-bold leading-8 mt-6">{{ $records }}</div>
                    <div class="text-base text-gray-600 mt-1">Inquilinos Totales</div>
                </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5">
                    <div class="flex">
                        <i data-feather="dollar-sign" class="report-box__icon text-theme-9"></i> 
                    </div>
                    <div class="text-3xl font-bold leading-8 mt-6">${{ number_format($total_collected, 2) }}</div>
                    <div class="text-base text-gray-600 mt-1">Total Recaudado</div>
                </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5">
                    <div class="flex">
                         <i data-feather="activity" class="report-box__icon text-theme-1"></i>
                    </div>
                    <div class="text-3xl font-bold leading-8 mt-6">${{ number_format($projected_mrr, 2) }}</div>
                    <div class="text-base text-gray-600 mt-1">Ingreso Mensual (MRR)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <button wire:click="openCreateModal" class="btn btn-primary shadow-md mr-2">
                    <i data-feather="plus" class="w-4 h-4 mr-2"></i> Crear Manualmente
                </button>
            </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <input wire:model.debounce.500ms="search" type="text" class="form-control w-56 box pr-10 placeholder-theme-13" placeholder="Buscar empresa...">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i> 
                </div>
            </div>
            <div class="mx-auto hidden md:block text-gray-600"></div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0">
                 <div class="flex items-center">
                    <input wire:model="filter" id="filter" type="checkbox" class="form-check-input border border-gray-500">
                    <label for="filter" class="text-gray-600 ml-2">Con Pagos</label>
                 </div>
            </div>
        </div>

        <!-- Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">EMPRESA / DOMINIO</th>
                        <th class="text-center whitespace-nowrap">PLAN / USO</th>
                        <th class="text-center whitespace-nowrap">PAGOS / ESTADO</th>
                        <th class="text-center whitespace-nowrap">ESTATUS</th>
                        <th class="text-center whitespace-nowrap">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                    <tr class="intro-x">
                        <td>
                            <a href="#" class="font-medium whitespace-nowrap">{{ $tenant->name }}</a> 
                            <div class="text-gray-600 text-xs whitespace-nowrap mt-0.5">{{ $tenant->id }}</div>
                            <div class="text-xs text-theme-1 mt-1">
                                @foreach($tenant->domains as $d)
                                <a href="//{{ $d->domain }}" target="_blank" class="hover:underline">{{ $d->domain }}</a>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="flex items-center whitespace-nowrap mb-1">
                                    <span class="px-2 py-1 rounded-full bg-theme-1 text-white text-xs mr-1">{{ ucfirst($tenant->suscription_type) }}</span>
                                    <span class="font-bold text-gray-800">${{ $tenant->amount }}</span>
                                </div>
                                @php $usage = $this->getUsage($tenant); @endphp
                                <div class="text-xs {{ ($usage['limit'] && $usage['used'] >= $usage['limit']) ? 'text-theme-6' : 'text-gray-600' }}">
                                    Uso: {{ $usage['used'] }} / {{ $usage['limit'] ?? '∞' }}
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $isDue = \Carbon\Carbon::parse($tenant->next_payment_due)->lessThanOrEqualTo(now());
                            @endphp
                            <div class="text-gray-600 text-xs whitespace-nowrap mt-0.5">
                                Próximo: {{ \Carbon\Carbon::parse($tenant->next_payment_due)->format('d/m/Y') }}
                                @if($isDue) <span class="text-theme-6 font-bold">(!)</span> @endif
                            </div>
                            @if($tenant->last_payment_date)
                            <div class="text-theme-9 text-xs">Último: {{ \Carbon\Carbon::parse($tenant->last_payment_date)->format('d/m/Y') }}</div>
                            @elseif($tenant->status == 1 && $tenant->suscription_type != 'Demo')
                            <div class="text-theme-6 text-xs font-bold animate-pulse">
                                <i data-feather="alert-circle" class="w-3 h-3 inline"></i> PAGO PENDIENTE
                            </div>
                            @else
                            <div class="text-gray-400 text-xs">Sin registros</div>
                            @endif
                        </td>
                        <td class="w-40">
                            <div class="flex items-center justify-center {{ $tenant->status == 1 ? 'text-theme-9' : 'text-theme-6' }}">
                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{ $tenant->status == 1 ? 'Activo' : 'Inactivo' }}
                            </div>
                            <button wire:click="setStatus('{{ $tenant->id }}')" class="btn btn-sm btn-secondary mt-1 w-24">
                                {{ $tenant->status == 1 ? 'Bloquear' : 'Activar' }}
                            </button>
                        </td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <button wire:click="editTenant('{{ $tenant->id }}')" class="flex items-center mr-3 text-theme-1"> 
                                    <i data-feather="edit" class="w-4 h-4 mr-1"></i> Editar 
                                </button>
                                            
                                <!-- Backup Action -->
                                <button wire:click="backupTenant('{{ $tenant->id }}')" class="flex items-center mr-3 text-theme-9" title="Respaldo (ZIP)">
                                    <span wire:loading.remove wire:target="backupTenant('{{ $tenant->id }}')">
                                        <i data-feather="download" class="w-4 h-4 mr-1"></i> Respaldo
                                    </span>
                                    <span wire:loading wire:target="backupTenant('{{ $tenant->id }}')">
                                        <i data-feather="loader" class="w-4 h-4 mr-1 animate-spin"></i> Generando...
                                    </span>
                                </button>

                                            
                                <button wire:click="viewHistory('{{ $tenant->id }}')" class="flex items-center mr-3 text-theme-1"> 
                                    <i data-feather="credit-card" class="w-4 h-4 mr-1"></i> Historial 
                                </button>
                                
                                <!-- Delete / Baja -->
                                <button wire:click="prepareOffboarding('{{ $tenant->id }}')" 
                                        class="flex items-center text-theme-6" title="Dar de Baja / Eliminar"> 
                                    <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> Eliminar 
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">No se encontraron inquilinos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
             {{ $tenants->links() }}
        </div>
    </div>

    {{-- PAYMENT HISTORY MODAL --}}
    @if($showHistoryModal)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.5);">
         <div class="intro-y box w-11/12 md:w-3/4 lg:w-1/2 p-5 shadow-xl" style="max-height: 90vh; overflow-y: auto;">
             <div class="flex items-center border-b border-gray-200 pb-5 mb-5">
                 <h2 class="font-medium text-base mr-auto">
                     Historial de Pagos: <span class="text-theme-1">{{ $selectedTenant->name }}</span>
                 </h2>
                 <button wire:click="closeHistory" class="text-gray-600 hover:text-gray-800">
                    <i data-feather="x" class="w-6 h-6"></i>
                 </button>
             </div>

             {{-- Manual Entry Form --}}
            <div class="alert alert-secondary show mb-5">
                <div class="flex items-center">
                    <div class="font-medium text-lg">Registrar Pago Manualmente</div>
                </div>
                <div class="mt-3 grid grid-cols-12 gap-2">
                    <input wire:model.defer="newPaymentAmount" type="number" step="0.01" class="form-control col-span-12 sm:col-span-3" placeholder="Monto (0.00)">
                    <input wire:model.defer="newPaymentDate" type="date" class="form-control col-span-12 sm:col-span-3">
                    <input wire:model.defer="newPaymentReference" type="text" class="form-control col-span-12 sm:col-span-3" placeholder="Ref / ID">
                    <button wire:click="storeManualPayment" class="btn btn-primary col-span-12 sm:col-span-3">
                        + Agregar
                    </button>
                </div>
                @error('newPaymentAmount') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                @error('newPaymentDate') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
            </div>

            <table class="table">
                <thead>
                    <tr class="bg-gray-200 text-gray-600">
                        <th class="whitespace-nowrap">FECHA</th>
                        <th class="whitespace-nowrap">MONTO</th>
                        <th class="whitespace-nowrap">CLIENTE</th>
                        <th class="whitespace-nowrap">REFERENCIA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentHistory as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-bold">${{ number_format($payment->amount / 100, 2) }}</td>
                        <td class="text-xs text-gray-600">
                            @if(isset($payment->payment_data['clientName']))
                                <div class="font-bold text-gray-700">{{ $payment->payment_data['clientName'] }}</div>
                                <div class="text-gray-500">{{ $payment->payment_data['phoneNumber'] ?? '--' }}</div>
                            @elseif(isset($payment->payment_data['email']))
                                 <div class="text-gray-500">{{ $payment->payment_data['email'] }}</div>
                            @else
                                <span class="text-gray-400 italic">--</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-500">ID: {{ substr($payment->payment_id ?? $payment->stripe_id, -8) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500">No hay pagos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
             
             <div class="mt-5 text-right">
                 <button wire:click="closeHistory" class="btn btn-outline-secondary w-20">Cerrar</button>
             </div>
         </div>
    </div>
    @endif

    {{-- OFFBOARDING MODAL --}}
    @if($showOffboardingModal)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10001; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.7);">
        <div class="intro-y box w-11/12 md:w-1/2 lg:w-1/3 p-5 shadow-xl text-center">
            
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 bg-theme-6 rounded-full flex items-center justify-center">
                    <i data-feather="alert-triangle" class="w-8 h-8 text-white"></i>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Dar de Baja: {{ $selectedTenant->name }}</h2>
            
            <div class="mt-4 mb-6 text-left p-4 bg-gray-100 rounded-lg">
                @if($offboardingStep === 1)
                    <p class="text-gray-600 mb-3">Estás a punto de iniciar el proceso de baja:</p>
                    <ul class="list-disc list-inside text-gray-500 text-sm mb-4">
                        <li>Generar un respaldo completo (Archivos + Base de Datos).</li>
                        <li>Eliminar permanentemente todos los datos.</li>
                    </ul>
                    <p class="font-bold text-gray-700">¿Deseas generar el respaldo ahora?</p>
                @elseif($offboardingStep === 2)
                    @if($isBackingUp)
                        <div class="flex flex-col items-center">
                            <i data-feather="loader" class="w-10 h-10 text-theme-1 animate-spin mb-3"></i>
                            <span class="text-gray-600">Generando respaldo...</span>
                        </div>
                    @else
                        <div class="bg-theme-9 text-white p-3 rounded mb-4 text-center">
                            ¡Respaldo Generado!
                        </div>
                        <div class="text-center mb-4">
                            @if($backupUrl)
                            <button wire:click="downloadBackup" class="btn btn-primary w-full">
                                <i data-feather="download" class="w-4 h-4 mr-2"></i> Descargar .ZIP
                            </button>
                            @else
                            <span class="text-theme-6">Error URL</span>
                            @endif
                        </div>
                        <div class="bg-theme-6 bg-opacity-10 border border-theme-6 text-theme-6 p-3 rounded text-sm">
                            <strong>Advertencia Final:</strong> <br>
                            Al confirmar, se eliminarán todos los datos permanentemente.
                        </div>
                    @endif
                @endif
            </div>

            <div class="flex justify-center gap-2">
                @if($offboardingStep === 1)
                    <button wire:click="startOffboardingBackup" class="btn btn-primary">Generar Respaldo</button>
                @elseif($offboardingStep === 2 && !$isBackingUp)
                    <button wire:click="confirmTenantDeletion" class="btn btn-danger">Confirmar Eliminación</button>
                @endif
                
                @if(!$isBackingUp)
                    <button wire:click="cancelOffboarding" class="btn btn-outline-secondary">Cancelar</button>
                @endif
            </div>

        </div>
    </div>
    @endif
    
    {{-- CREATE TENANT MODAL --}}
    @if($createModalOpen)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.5);">
        <div class="intro-y box w-11/12 md:w-1/2 p-5 shadow-xl">
             <div class="flex items-center border-b border-gray-200 pb-5 mb-5">
                 <h2 class="font-medium text-base mr-auto">Crear Tenant Manualmente</h2>
                 <button wire:click="closeModals" class="text-gray-600 hover:text-gray-800"><i data-feather="x" class="w-6 h-6"></i></button>
             </div>
             <form wire:submit.prevent="createTenant">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Nombre Empresa</label>
                        <input wire:model="t_name" type="text" class="form-control">
                        @error('t_name') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Subdominio (ID)</label>
                        <div class="input-group">
                            <input wire:model="t_subdomain" type="text" class="form-control" placeholder="ej: cliente1">
                        </div>
                        @error('t_subdomain') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Email Admin</label>
                        <input wire:model="t_email" type="email" class="form-control">
                        @error('t_email') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Contraseña Admin</label>
                        <input wire:model="t_password" type="password" class="form-control">
                        @error('t_password') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Plan Inicial</label>
                        <select wire:model="t_plan" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($plans as $p)
                                <option value="{{ $p->name }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('t_plan') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Precio Acordado ($)</label>
                        <input wire:model="t_amount" type="number" step="0.01" class="form-control">
                        @error('t_amount') <span class="text-theme-6 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="text-right mt-5">
                    <button type="button" wire:click="closeModals" class="btn btn-outline-secondary w-20 mr-1">Cancelar</button>
                    <button type="submit" class="btn btn-primary w-24">Crear</button>
                </div>
             </form>
         </div>
    </div>
    @endif

    {{-- EDIT TENANT MODAL --}}
    @if($editModalOpen)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.5);">
        <div class="intro-y box w-11/12 md:w-1/2 p-5 shadow-xl">
             <div class="flex items-center border-b border-gray-200 pb-5 mb-5">
                 <h2 class="font-medium text-base mr-auto">Editar Tenant</h2>
                 <button wire:click="closeModals" class="text-gray-600 hover:text-gray-800"><i data-feather="x" class="w-6 h-6"></i></button>
             </div>
             <form wire:submit.prevent="updateTenant">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Nombre Empresa</label>
                        <input wire:model="t_name" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                         <label class="form-label">Subdominio (Solo Lectura)</label>
                         <input wire:model="t_subdomain" type="text" class="form-control" disabled>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Plan Actual</label>
                         <select wire:model="t_plan" class="form-select">
                            @foreach($plans as $p)
                                <option value="{{ $p->name }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Precio ($)</label>
                        <input wire:model="t_amount" type="number" step="0.01" class="form-control">
                    </div>
                     <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Próximo Vencimiento</label>
                        <input wire:model="t_bill_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="text-right mt-5">
                    <button type="button" wire:click="closeModals" class="btn btn-outline-secondary w-20 mr-1">Cancelar</button>
                    <button type="submit" class="btn btn-primary w-24">Guardar</button>
                </div>
             </form>
         </div>
    </div>
    @endif
    
    <script>
        // Re-initialize feather icons after Livewire updates
        document.addEventListener('livewire:load', function () {
            feather.replace();
            
            // Listen for Tenant Created Redirect
            @this.on('tenant-created', (url) => {
                console.log('Redirecting to tenant: ' + url);
                window.location.href = url;
            });

            window.addEventListener('download-backup', event => {
                console.log('Downloading backup: ' + event.detail.url);
                window.location.href = event.detail.url;
            });
        });
        document.addEventListener('livewire:update', function () {
            feather.replace();
        });
    </script>
</div>
