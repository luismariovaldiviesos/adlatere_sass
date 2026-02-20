<div>
    {{-- <script src="https://js.stripe.com/v3/"></script> --}}

    <style>
        /* estilos para el formulario de pago que renderiza stripe */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

        .card_box {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin: auto;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }

        .card_input {
            width: 100%;
            box-sizing: border-box;
            border: #ddd solid 1px;
            border-radius: 6px;
            color: #555;
            padding: 8px;
            margin-top: 2px;
            margin-bottom: 14px;
            outline: none;
            font-family: inherit;
        }

        .amount {
            font-size: 40px !important;
        }
    </style>

    {{-- plans --}}
    <div class="row" id="plans-section">
        @foreach ($plans as $plan)
        <div class="col-lg-3 col-md-6">
            <div class="pricingtable">
                <div class="pricingtable-header">
                    <h3 class="title">{{ $plan->name }}</h3>
                </div>
                <div class="price-value">
                    <span class="currency">$</span>
                    <span class="amount">{{ ($plan->price) }}</span>
                </div>
                <ul class="pricing-content" style="list-style: none; padding-left: 0;">
                     @foreach(explode("\n", $plan->description) as $feature)
                        @if(trim($feature))
                            <li class="mb-2 d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check text-primary me-2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ trim($feature) }}</span>
                            </li>
                        @endif
                     @endforeach
                </ul>
                <div class="pricingtable-signup">
                    <button type="button" class="btn btn-primary btn-lg" wire:click="selectPlan({{ $plan->id }})"
                        data-plan-price="{{ intval($plan->price) }}">
                        Registrate
                    </button>

                </div>
            </div>
        </div>
        @endforeach
    </div>


    {{-- modal planes --}}
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><b>Completar Suscripción</b></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    @if (session()->has('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    @if($paymentUrl)
                        <div class="text-center" wire:ignore>
                            <h4 class="text-success mb-3">¡Registro Inicial Exitoso!</h4>
                            <p>Para completar la activación de tu plan, realiza el pago seguro:</p>
                            
                            <a href="{{ $paymentUrl }}" target="_blank" class="btn btn-success btn-lg w-100 mb-3">
                                Pagar Ahora y Activar (Nueva Pestaña)
                            </a>
                            
                            <div class="mt-3">
                                <p class="small text-muted">¿No funciona el botón? Copia y pega este enlace:</p>
                                <input type="text" class="form-control text-center" value="{{ $paymentUrl }}" readonly onclick="this.select()">
                            </div>
                        </div>
                    @else
                    <form wire:submit.prevent="register">
                        <div class="mb-3">
                            <label class="form-label">Nombre de tu Empresa / Negocio</label>
                            <input class="form-control" type="text" wire:model="company_name" placeholder="Mi Empresa S.A."
                                required>
                            @error('company_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre del usuario admin (agregado para coincidir con user::create en Payator.php) --}}
                        {{-- Si Payator.php usa 'company_name' para el user name, esta bien. Si no, debería agregar input 'name' --}}
                        {{-- En Payator.php actual usé 'name' => $this->company_name, así que está bien por ahora. --}}
                        
                        <div class="mb-3">
                            <label class="form-label">Subdominio Deseado</label>
                            <div class="input-group">
                                <input type="text" class="form-control" wire:model="tenant_id" placeholder="miempresa"
                                    required>
                                <span class="input-group-text">.facta_saas.test</span>
                            </div>
                            @error('tenant_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cédula / RUC</label>
                            <input class="form-control" type="text" wire:model="ci" placeholder="1712345678" required>
                            @error('ci') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono / Celular</label>
                            <input class="form-control" type="text" wire:model="phone" placeholder="0991234567">
                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tu Correo Electrónico</label>
                            <input class="form-control" type="email" wire:model="email" placeholder="admin@miempresa.com"
                                required>
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input class="form-control" type="password" wire:model="password" required>
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input class="form-control" type="password" wire:model="password_confirmation" required>
                        </div>
                   
                        
                        <div class="card_box mt-4">
                             <h5 class="fw-bold text-info" id='planDescription'>
                                {{ $selectedPlan ? $selectedPlan->name . ' / $' . number_format($selectedPlan->price, 2) : '' }}
                             </h5>
                            
                            {{-- Botón de registro --}}
                            <div class="pricing-btn">
                                <button type="button" class="btn btn-primary w-100" wire:click="register" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        @if($amount > 0)
                                            Pagar ${{ number_format($amount, 2) }} y Crear
                                        @else
                                            Crear Espacio Gratis
                                        @endif
                                    </span>
                                    <span wire:loading>Creando...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                    @endif

                </div>

            </div>
        </div>
    </div>

    <script>
        window.addEventListener('init-payment', event => {
            console.log('Intento de redirección automática: ', event.detail.url);
            if (event.detail.url) {
                // Try to redirect
                window.location.href = event.detail.url;
            }
        });

        document.addEventListener('livewire:load', function () {
             // Scripts específicos si se necesitan
        });
        
        // Re-iniciar modal cuando se selecciona plan si es necesario
    </script>
    
    <!-- WhatsApp Support Button (Payator Direct) -->
    <a href="https://wa.me/593987308688?text=Hola,%20tengo%20consultas%20sobre%20los%20planes" class="whatsapp-float-payator" target="_blank" title="Consultas Comerciales">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#FFF" class="me-2">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
        <span class="fw-bold">Chatea con nosotros: 0987308688</span>
    </a>

    <style>
        .whatsapp-float-payator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 16px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .whatsapp-float-payator:hover {
            background-color: #128C7E;
            color: #FFF;
            transform: scale(1.02);
            box-shadow: 2px 4px 8px rgba(0,0,0,0.4);
        }
    </style>

</div>
