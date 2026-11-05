<div>
    <div class="plans-registration">
        @if($plans->isEmpty())
            <div class="plans-empty">
                Aún no hay planes configurados en el panel central.
            </div>
        @else
            <div class="plans-grid">
                @foreach($plans as $plan)
                    <article class="plan-card">
                        <div class="plan-label">{{ $plan->name }}</div>
                        <div class="plan-price">
                            <strong>${{ number_format($plan->price, 2) }}</strong>
                            <span>/ mes</span>
                        </div>
                        <p class="plan-copy">Plan {{ $plan->name }} para tu despacho.</p>

                        @php
                            $caracteristicas = collect(explode("\n", (string) $plan->description))
                                ->map(fn ($item) => trim($item))
                                ->filter();
                        @endphp

                        @if($caracteristicas->isNotEmpty())
                            <ul class="plan-features">
                                @foreach($caracteristicas as $caracteristica)
                                    <li>
                                        <svg class="icon"><use href="#i-check"/></svg>
                                        <span>{{ $caracteristica }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <button type="button" class="btn btn-primary" wire:click="selectPlan({{ $plan->id }})">
                            Elegir {{ $plan->name }}
                            <svg class="icon" style="width:14px;height:14px"><use href="#i-arrow"/></svg>
                        </button>
                    </article>
                @endforeach
            </div>
            <p class="plans-note">Los planes y precios se administran desde el panel central de Adlatere SaaS.</p>
        @endif
    </div>

    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#bcd0f8;">Registro de despacho</div>
                        <h5 class="modal-title mb-0">Crear espacio en Adlatere SaaS</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    @if($paymentUrl)
                        <div class="payment-created" wire:ignore>
                            <h4>Espacio creado</h4>
                            <p class="text-muted mb-3">Para activar tu plan, completa el pago seguro:</p>
                            <a href="{{ $paymentUrl }}" target="_blank" class="btn btn-primary w-100 mb-3">
                                Pagar ahora y activar
                            </a>
                            <div class="link-box">
                                <p class="small text-muted mb-1">Si el botón no funciona, copia este enlace:</p>
                                <input type="text" class="form-control text-center" value="{{ $paymentUrl }}" readonly onclick="this.select()">
                            </div>
                        </div>
                    @else
                        @if($selectedPlan)
                            <div class="reg-summary">
                                Plan seleccionado: <strong>{{ $selectedPlan->name }}</strong>
                                · ${{ number_format($selectedPlan->price, 2) }} / mes
                            </div>
                        @endif

                        <form wire:submit.prevent="register">
                            <div class="reg-grid">
                                <div class="mb-3 full">
                                    <label class="form-label">Nombre del despacho</label>
                                    <input type="text" class="form-control" wire:model="company_name" placeholder="Ej. Rosales & Tello Abogados" required>
                                    @error('company_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Subdominio</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="tenant_id" placeholder="rosalestello" required>
                                        <span class="input-group-text">.adlatere.ec</span>
                                    </div>
                                    @error('tenant_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Cédula / RUC</label>
                                    <input type="text" class="form-control" wire:model="ci" placeholder="1712345678" required>
                                    @error('ci') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Teléfono / Celular</label>
                                    <input type="text" class="form-control" wire:model="phone" placeholder="0991234567">
                                    @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Correo del administrador</label>
                                    <input type="email" class="form-control" wire:model="email" placeholder="admin@rosalestello.com" required>
                                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" wire:model="password" required>
                                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirmar contraseña</label>
                                    <input type="password" class="form-control" wire:model="password_confirmation" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-1" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="register">
                                    @if($amount > 0)
                                        Pagar ${{ number_format($amount, 2) }} y crear
                                    @else
                                        Crear espacio gratis
                                    @endif
                                </span>
                                <span wire:loading wire:target="register">Creando espacio...</span>
                            </button>

                            <p class="legal-note text-center mt-3 mb-0">
                                Al continuar aceptas los términos del servicio y la política de privacidad.<br>
                                Este formulario es parte de una maqueta funcional del registro SaaS.
                            </p>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('init-payment', event => {
            if (event.detail.url) {
                window.location.href = event.detail.url;
            }
        });
    </script>
</div>
