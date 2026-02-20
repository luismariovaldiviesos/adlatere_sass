<div>
    @can('ver_dash')
    <div class="intro-y grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 md:col-span-3">
            <div class="intro-y box">
                <h6 class="text-center font-bold">Elige el Año de Consulta</h6>
                <select wire:model="year" class="form-select form-select-lg">
                    @foreach($listYears as $y)
                    <option value="{{$y}}">{{$y}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <br>
          <div class="col-span-12 md:col-span-3">
            <div class="intro-y box">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold leading-8 mt-6">{{ strtoupper(tenant('suscription_type') ?? 'GRATUITO') }}</div>
                    <div class="text-base text-gray-600 mt-1">Plan Actual</div>
                    <div class="mt-2 text-gray-600 text-xs">Tenant ID: {{ tenant('id') }}</div>

                    @php
                        $tenant = tenant();
                        $count = $tenant->getCurrentCycleInvoiceCount();
                        $limit = $tenant->getInvoiceLimit();
                        
                        // Dates Logic
                        $lastPayment = \Carbon\Carbon::parse($tenant->bill_date);
                        $expiration = $lastPayment->copy()->addMonth(); // Vence al mes del pago
                        $daysDiff = intval(now()->diffInDays($expiration, false));
                        $isOverdue = $expiration->isPast();

                        // Payment URL (Fixed to Production domain)
                        $paymentUrl = 'https://facta.ec/payment/renewal/' . $tenant->id;
                    @endphp

                    <div class="mt-4 border-t border-gray-200 pt-4 text-left">
                        <div class="flex justify-between mb-1">
                             <span class="text-gray-600 text-xs">Facturas:</span>
                             <span class="font-bold text-xs {{ $limit && $count >= $limit ? 'text-theme-6' : 'text-theme-1' }}">
                                 {{ $count }} / {{ $limit ?? '∞' }}
                             </span>
                        </div>
                        <div class="flex justify-between mb-1">
                             <span class="text-gray-600 text-xs">Último Pago:</span>
                             <span class="font-bold text-xs">{{ $lastPayment->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                             <span class="text-gray-600 text-xs">Vencimiento:</span>
                             <span class="font-bold text-xs">{{ $expiration->format('d/m/Y') }}</span>
                        </div>

                         <div class="text-center mt-3">
                            @if($isOverdue)
                                <div class="mb-2">
                                     <span class="py-1 px-2 rounded-full text-xs bg-theme-6 text-white font-medium">
                                        ¡Vencido hace {{ abs($daysDiff) }} días!
                                     </span>
                                </div>
                            @else
                                <div class="mb-2">
                                    <span class="py-1 px-2 rounded-full text-xs bg-theme-9 text-white font-medium">
                                        Vence en {{ $daysDiff }} días
                                    </span>
                                </div>
                            @endif

                             <button type="button" wire:click="openModal" class="btn btn-sm btn-primary w-full shadow-md">
                                <i data-feather="credit-card" class="w-4 h-4 mr-2"></i> Pagar / Renovar
                            </button>
                        </div>
                    </div>
                </div>
        </div>
          </div>
    </div>

   

    <div class="intro-y grid grid-cols-12 gap-6 mt-5">


        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box">
                <h4 class="p-3 text-center text-theme-1 font-bold">TOP 5 MAS VENDIDOS</h4>
                <div id="chartTop5">
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6">
            <div class="intro-y box ">
                <h4 class="p-3 text-center text-theme-1 font-bold">VENTAS DE LA SEMANA</h4>
                <div id="chartArea">
                </div>
            </div>
        </div>

    </div>

    <div class="intro-y grid grid-cols-12 pt-5 gap-6">
        <div class="col-span-12 lg:col-span-8">
            <div class="intro-y box ">
                <h4 class="p-3 text-center text-theme-1 font-bold"> VENTAS POR MES AÑO  {{$year}}</h4>
                <div id="chartMonth">
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-4">
            <div class="intro-y box ">
                <h4 class="p-3 text-center text-theme-1 font-bold"> VENTAS POR FORMA DE PAGO</h4>
                <div id="chartPaymentMethod">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Renovación (Livewire Controlled) -->
    @if($modalOpen)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.5);">
        <div class="intro-y box w-11/12 md:w-1/3 p-5 shadow-xl bg-white" style="max-width: 500px;">
             <div class="flex items-center border-b border-gray-200 pb-3 mb-5">
                 <h2 class="font-medium text-base mr-auto">Opciones de Renovación</h2>
                 <button wire:click="closeModal" class="text-gray-600 hover:text-gray-800"><i data-feather="x" class="w-6 h-6"></i></button>
             </div>

             <div class="text-center mb-5">
                <div class="text-2xl font-bold text-theme-1">{{ strtoupper(tenant('suscription_type') ?? 'GRATUITO') }}</div>
                <div class="text-base text-gray-600">Monto a pagar: <b>${{ number_format($tenant->amount ?? 0, 2) }}</b></div>
            </div>

            <div x-data="{ tab: 'card' }">
                <div class="flex justify-center border-b border-gray-200 mb-4">
                    <button @click="tab = 'card'" :class="tab === 'card' ? 'border-theme-1 text-theme-1' : 'border-transparent text-gray-600 hover:text-gray-800'" class="py-2 px-4 border-b-2 font-medium text-sm focus:outline-none transition-all">Tarjeta (PayPhone)</button>
                    <button @click="tab = 'transfer'" :class="tab === 'transfer' ? 'border-theme-1 text-theme-1' : 'border-transparent text-gray-600 hover:text-gray-800'" class="py-2 px-4 border-b-2 font-medium text-sm focus:outline-none transition-all">Transferencia</button>
                </div>

                <!-- PayPhone Content -->
                <div x-show="tab === 'card'" class="text-center p-2">
                    <div class="flex justify-center mb-4">
                        <img src="https://www.payphone.app/wp-content/uploads/2021/05/Logo-PayPhone.png" class="h-8" alt="PayPhone">
                    </div>
                    <p class="text-xs text-gray-600 mb-4 px-4">Serás redirigido a la pasarela de pagos segura de PayPhone para procesar tu renovación.</p>
                    <a href="{{ $paymentUrl }}" target="_blank" class="btn btn-primary w-full">
                        Ir a Pagar con PayPhone
                    </a>
                </div>

                <!-- Transfer Content -->
                <div x-show="tab === 'transfer'" class="p-2">
                    <div class="bg-gray-100 p-4 rounded-md mb-4 border border-gray-200">
                        <h4 class="font-bold text-theme-1 text-xs mb-2 text-center uppercase tracking-wider">Depositar o Transferir</h4>
                        <div class="text-xs space-y-1">
                            <p class="flex justify-between"><span>Banco:</span> <b>Banco Pichincha</b></p>
                            <p class="flex justify-between"><span>Tipo:</span> <b>Cta. Corriente</b></p>
                            <p class="flex justify-between"><span>Número:</span> <b>2100223344</b></p>
                            <p class="flex justify-between"><span>Beneficiario:</span> <b>FACTA S.A.</b></p>
                            <p class="flex justify-between"><span>CI/RUC:</span> <b>1722223334001</b></p>
                        </div>
                    </div>
                    @php
                        $wpMsg = "Hola, adjunto comprobante de pago para renovación del Tenant: " . tenant('id') . " - Plan: " . tenant('suscription_type');
                        $wpUrl = "https://wa.me/593987308688?text=" . urlencode($wpMsg);
                    @endphp
                    <a href="{{ $wpUrl }}" target="_blank" class="btn btn-success text-white w-full shadow-md">
                        <i data-feather="message-circle" class="w-4 h-4 mr-2"></i> Notificar por WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @include('livewire.dash.scripts')
    @else
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endcan

</div>
