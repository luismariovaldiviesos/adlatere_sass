<div>
    {{-- TITULO Y METRICAS --}}
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            <i class="fas fa-hand-holding-usd text-primary mr-2"></i> Gestión Financiera y Cobros de Juicios
        </h2>
    </div>

    {{-- TARJETAS KPI RESUMEN --}}
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5 border-l-4 border-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-slate-500">Total Cartera Acordada</span>
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <i class="fas fa-file-invoice-dollar text-lg"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-800 mt-4">$ {{ number_format($totalPactado, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Honorarios + Gastos registrados</div>
                </div>
            </div>
        </div>

        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5 border-l-4 border-success">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-slate-500">Total Recaudado</span>
                        <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center text-success">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-success mt-4">$ {{ number_format($totalRecaudado, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Abonos cobrados y aprobados</div>
                </div>
            </div>
        </div>

        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5 border-l-4 border-warning">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-slate-500">Saldo Pendiente Global</span>
                        <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center text-warning">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-warning-800 mt-4">$ {{ number_format($totalSaldoPendiente, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Cuentas por cobrar a clientes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BARRA DE BUSQUEDA Y FILTROS --}}
    <div class="intro-y box p-5 mt-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="w-full sm:w-1/2 relative text-slate-500">
                <input type="text" wire:model.debounce.400ms="search" class="form-control w-full pr-10" placeholder="Buscar por proceso SATJE, cliente o asunto...">
                <i class="fas fa-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0"></i>
            </div>
            <div class="text-xs text-slate-500">
                Mostrando juicios con acuerdos y pagos gestionables
            </div>
        </div>
    </div>

    {{-- LISTADO DE JUICIOS Y SU ESTADO FINANCIERO --}}
    <div class="intro-y box overflow-x-auto mt-6">
        <table class="table table-report table-auto">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">PROCESO / ASUNTO</th>
                    <th class="whitespace-nowrap">CLIENTE(S)</th>
                    <th class="whitespace-nowrap text-right">PACTADO</th>
                    <th class="whitespace-nowrap text-right">ABONADO</th>
                    <th class="whitespace-nowrap text-right">SALDO</th>
                    <th class="whitespace-nowrap text-center">ESTADO</th>
                    <th class="whitespace-nowrap text-center">GESTIÓN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($juicios as $j)
                    @php
                        $fin = $j->finanza;
                        $pactado = $fin ? ($fin->honorarios_totales + $fin->gastos_extras) : 0;
                        $pagado = $fin ? $fin->total_pagado : 0;
                        $saldo = max(0, $pactado - $pagado);
                        $alDia = ($pactado > 0 && $saldo <= 0);
                        $sinConfig = ($pactado <= 0 && $pagado <= 0);
                    @endphp
                    <tr class="intro-x hover:bg-slate-50">
                        <td>
                            <div class="font-bold text-primary">{{ $j->cod_satje ?: 'Borrador / Sin SATJE' }}</div>
                            <div class="text-xs text-slate-500">{{ $j->asunto->nombre ?? 'Sin Asunto' }}</div>
                            @if($j->abogados->count() > 0)
                                <div class="text-xs text-slate-400 mt-1">
                                    <i class="fas fa-user-tie mr-1"></i> {{ $j->abogados->first()->name }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $clientes = $j->actores->merge($j->demandados)->filter(fn($p) => !empty($p->pivot->es_cliente));
                            @endphp
                            @if($clientes->count() > 0)
                                @foreach($clientes as $c)
                                    <div class="text-slate-800 font-medium text-sm">{{ $c->businame }}</div>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 italic">Sin clientes marcados</span>
                            @endif
                        </td>
                        <td class="text-right font-medium text-slate-700">
                            $ {{ number_format($pactado, 2) }}
                        </td>
                        <td class="text-right font-bold text-success">
                            $ {{ number_format($pagado, 2) }}
                        </td>
                        <td class="text-right font-bold {{ $saldo > 0 ? 'text-warning-800' : 'text-slate-400' }}">
                            $ {{ number_format($saldo, 2) }}
                        </td>
                        <td class="text-center">
                            @if($sinConfig)
                                <span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600">Sin Configurar</span>
                            @elseif($alDia)
                                <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700">Al Día</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-800">Con Saldo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" wire:click="abrirGestion({{ $j->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-primary flex items-center mx-auto shadow-sm">
                                <span wire:loading.remove wire:target="abrirGestion({{ $j->id }})">
                                    <i class="fas fa-coins mr-1"></i> Cobrar / Gestionar
                                </span>
                                <span wire:loading wire:target="abrirGestion({{ $j->id }})">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Abriendo…
                                </span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500">
                            No se encontraron juicios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-slate-200">
            {{ $juicios->links() }}
        </div>
    </div>

    {{-- MODAL DE GESTIÓN FINANCIERA (HONORARIOS + ABONOS + FACTURACIÓN) --}}
    @if($showModalGestion && $juicioSeleccionado)
        <div id="modal-finanzas-overlay" wire:click="cerrarGestion" class="fixed top-0 left-0 w-full h-full flex items-center justify-center p-4" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 100000 !important; background-color: rgba(0,0,0,0.65);">
            <div onclick="event.stopPropagation()" class="bg-white rounded-xl shadow-2xl flex flex-col border-2 border-slate-300" style="width: 94%; max-width: 950px; max-height: 90vh; overflow-y: auto; background-color: #ffffff; z-index: 100001; position: relative;">
                
                {{-- Cabecera Modal --}}
                <div class="p-5 border-b border-slate-200 flex justify-between items-center sticky top-0 z-10" style="background-color: #f8fafc; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem;">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            <i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Cobros y Finanzas: {{ $juicioSeleccionado->cod_satje ?: 'Juicio #' . $juicioSeleccionado->id }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Asunto: {{ $juicioSeleccionado->asunto?->nombre ?? 'N/A' }} | 
                            Materia: {{ $juicioSeleccionado->asunto?->procedimiento?->materia?->nombre ?? 'N/A' }}
                        </p>
                    </div>
                    <button type="button" wire:click="cerrarGestion" class="btn btn-outline-secondary btn-sm px-4 font-bold text-sm">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                </div>

                {{-- Cuerpo con Scroll --}}
                <div class="p-6 overflow-y-auto space-y-8 flex-1">
                    
                    {{-- TARJETAS RESUMEN DEL CASO --}}
                    @if($juicioSeleccionado->finanza)
                        @php
                            $modalPactado = $juicioSeleccionado->finanza->honorarios_totales + $juicioSeleccionado->finanza->gastos_extras;
                            $modalPagado = $juicioSeleccionado->finanza->total_pagado;
                            $modalSaldo = max(0, $modalPactado - $modalPagado);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase">Deuda Total Acordada</div>
                                <div class="mt-1 text-xl font-bold text-slate-800">$ {{ number_format($modalPactado, 2) }}</div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="text-xs font-semibold text-green-700 uppercase">Total Abonado</div>
                                <div class="mt-1 text-xl font-bold text-green-800">$ {{ number_format($modalPagado, 2) }}</div>
                            </div>
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <div class="text-xs font-semibold text-amber-800 uppercase">Saldo Pendiente</div>
                                <div class="mt-1 text-xl font-bold text-amber-900">$ {{ number_format($modalSaldo, 2) }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- BLOQUE 1: PACTO DE HONORARIOS Y GASTOS --}}
                    <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h4 class="font-bold text-slate-700 text-base">
                                <i class="fas fa-handshake mr-1 text-primary"></i> 1. Acuerdo Económico del Juicio
                            </h4>
                            <span class="text-xs text-slate-400">Pactar montos de honorarios y gastos extras</span>
                        </div>
                        <div class="grid grid-cols-12 gap-4 items-end">
                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label text-xs font-semibold text-slate-600">Honorarios Acordados ($)</label>
                                <input type="number" step="0.01" wire:model.defer="fin_honorarios" class="form-control" placeholder="0.00">
                                @error('fin_honorarios') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label text-xs font-semibold text-slate-600">Gastos Extras Estimados ($)</label>
                                <input type="number" step="0.01" wire:model.defer="fin_gastos" class="form-control" placeholder="0.00">
                                @error('fin_gastos') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label class="form-label text-xs font-semibold text-slate-600">Condiciones / Notas del Acuerdo</label>
                                <input type="text" wire:model.defer="fin_notas_acuerdo" class="form-control" placeholder="Ej: Pago en 3 cuotas mensuales...">
                            </div>
                            <div class="col-span-12 sm:col-span-2">
                                <button type="button" wire:click="saveFinanzas" class="btn btn-secondary w-full">
                                    <i class="fas fa-save mr-1"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE 2: REGISTRAR NUEVO ABONO --}}
                    <div class="bg-white p-5 rounded-lg border border-primary/30 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h4 class="font-bold text-primary text-base">
                                <i class="fas fa-cash-register mr-1"></i> 2. Registrar Nuevo Abono
                            </h4>
                            <span class="text-xs text-slate-400">Ingreso de dinero para caja / banco</span>
                        </div>

                        <div class="grid grid-cols-12 gap-4 items-end">
                            <div class="col-span-12 sm:col-span-4">
                                <label class="form-label text-xs font-semibold text-slate-600">Cliente que realiza el pago (*)</label>
                                <select wire:model.defer="pago_customer_id" class="form-select">
                                    <option value="">Seleccione cliente...</option>
                                    @php
                                        $pagadores = $juicioSeleccionado->actores->merge($juicioSeleccionado->demandados)
                                            ->filter(fn($p) => !empty($p->pivot->es_cliente))->unique('id');
                                    @endphp
                                    @foreach($pagadores as $pag)
                                        <option value="{{ $pag->id }}">{{ $pag->businame }} ({{ ucfirst($pag->pivot->rol ?? 'Cliente') }})</option>
                                    @endforeach
                                    @if($pagadores->count() === 0)
                                        @foreach($juicioSeleccionado->actores as $act)
                                            <option value="{{ $act->id }}">{{ $act->businame }} (Actor)</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('pago_customer_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-2">
                                <label class="form-label text-xs font-semibold text-slate-600">Monto ($) (*)</label>
                                <input type="number" step="0.01" wire:model.defer="pago_monto" class="form-control" placeholder="0.00">
                                @error('pago_monto') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label text-xs font-semibold text-slate-600">Fecha de Pago (*)</label>
                                <input type="date" wire:model.defer="pago_fecha" class="form-control">
                                @error('pago_fecha') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label text-xs font-semibold text-slate-600">Método de Pago</label>
                                <select wire:model.defer="pago_metodo" class="form-select">
                                    <option value="Transferencia">Transferencia Bancaria</option>
                                    <option value="Efectivo">Efectivo en Caja</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Tarjeta">Tarjeta Débito/Crédito</option>
                                </select>
                            </div>

                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label text-xs font-semibold text-slate-600">N° Transacción / Referencia</label>
                                <input type="text" wire:model.defer="pago_referencia" class="form-control" placeholder="Ej: 00987452">
                            </div>

                            <div class="col-span-12 sm:col-span-5">
                                <label class="form-label text-xs font-semibold text-slate-600">Comprobante Físico / Voucher (Opcional)</label>
                                <input type="file" wire:model="pago_comprobante" class="form-control">
                                <div wire:loading wire:target="pago_comprobante" class="text-xs text-primary mt-1">Cargando archivo...</div>
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <label class="form-label text-xs font-semibold text-slate-600">Notas / Observación</label>
                                <input type="text" wire:model.defer="pago_notas" class="form-control" placeholder="Ej: Pago cuota inicial">
                            </div>

                            <div class="col-span-12 flex justify-end pt-2">
                                <button type="button" wire:click="savePago" wire:loading.attr="disabled" wire:target="pago_comprobante" class="btn btn-primary px-8">
                                    <i class="fas fa-check mr-2"></i> Registrar Abono
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE 3: HISTORIAL DE ABONOS REGISTRADOS --}}
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-700 text-base">
                            <i class="fas fa-history mr-1 text-primary"></i> 3. Historial de Abonos y Facturación
                        </h4>

                        @if($juicioSeleccionado->finanza && $juicioSeleccionado->finanza->pagos->count() > 0)
                            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                <table class="table table-striped table-sm">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <th>FECHA</th>
                                            <th>CLIENTE</th>
                                            <th>MÉTODO / REF</th>
                                            <th class="text-right">MONTO</th>
                                            <th class="text-center">COMPROBANTE</th>
                                            <th class="text-center">FACTURACIÓN</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($juicioSeleccionado->finanza->pagos->sortByDesc('fecha_pago') as $p)
                                            <tr>
                                                <td class="whitespace-nowrap font-medium">
                                                    {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <span class="font-bold text-slate-800">{{ $p->cliente->businame ?? 'N/A' }}</span>
                                                    @if($p->notas)
                                                        <div class="text-xs text-slate-500">{{ $p->notas }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="font-medium">{{ $p->metodo_pago }}</span>
                                                    @if($p->referencia_transaccion)
                                                        <div class="text-xs text-slate-400">Ref: {{ $p->referencia_transaccion }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-right font-bold text-success whitespace-nowrap">
                                                    $ {{ number_format($p->monto, 2) }}
                                                    @if($p->estado === 'Anulado')
                                                        <br><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">ANULADO</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($p->comprobante_ruta)
                                                        <a href="{{ route('tenant.media', ['path' => $p->comprobante_ruta]) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                                            <i class="fas fa-eye mr-1"></i> Ver
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-slate-400">Sin archivo</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($p->factura_id)
                                                        <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700 flex items-center justify-center w-28 mx-auto">
                                                            <i class="fas fa-receipt mr-1"></i> Facturado
                                                        </span>
                                                    @else
                                                        <a href="{{ route('facturas', ['cliente_id' => $p->customer_id, 'monto_pago' => $p->monto, 'pago_id' => $p->id]) }}" target="_blank" class="btn btn-xs btn-success text-white flex items-center justify-center w-28 mx-auto">
                                                            <i class="fas fa-file-invoice mr-1"></i> Facturar SRI
                                                        </a>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($p->estado !== 'Anulado' && !$p->factura_id)
                                                        <button type="button" onclick="confirm('¿Seguro que desea ANULAR este pago? El registro se conservará como auditoría y se sumará al saldo pendiente.') || event.stopImmediatePropagation()" wire:click="destroyPago({{ $p->id }})" class="btn btn-xs btn-outline-danger" title="Anular Abono">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-6 bg-slate-50 border border-dashed rounded-lg text-slate-500 text-xs">
                                Aún no se han registrado abonos para este caso.
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Pie Modal --}}
                <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
                    <button type="button" wire:click="cerrarGestion" class="btn btn-secondary px-6">
                        Cerrar Panel
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
