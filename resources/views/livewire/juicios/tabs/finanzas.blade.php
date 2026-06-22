<div class="p-8 space-y-8">

    {{-- TARJETAS DE RESUMEN (Cálculos dinámicos) --}}
    @if(isset($juicio) && $juicio->finanza)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                <div class="text-slate-500 font-medium">Deuda Total (Honorarios + Gastos)</div>
                <div class="mt-2 text-2xl font-bold text-slate-800">
                    $ {{ number_format($juicio->finanza->honorarios_totales + $juicio->finanza->gastos_extras, 2) }}
                </div>
            </div>
            <div class="bg-primary/10 border border-primary/20 rounded-lg p-5">
                <div class="text-primary font-medium">Total Abonado</div>
                <div class="mt-2 text-2xl font-bold text-primary">
                    $ {{ number_format($juicio->finanza->total_pagado, 2) }}
                </div>
            </div>
            <div class="bg-warning/10 border border-warning/20 rounded-lg p-5">
                <div class="text-warning-800 font-medium">Saldo Pendiente</div>
                <div class="mt-2 text-2xl font-bold text-warning-800">
                    $ {{ number_format($juicio->finanza->saldo, 2) }}
                </div>
            </div>
        </div>
    @endif

    {{-- BLOQUE 1: CONFIGURAR HONORARIOS --}}
    <div class="grid grid-cols-12 gap-6 items-end bg-slate-50 p-6 rounded-lg border border-slate-200">
        <div class="col-span-12">
            <h3 class="text-lg font-bold">Configurar Costos del Juicio</h3>
        </div>
        
        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Honorarios Acordados ($)</label>
            <input type="number" step="0.01" wire:model.defer="fin_honorarios" class="form-control h-12 text-lg" placeholder="0.00">
            @error('fin_honorarios') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Gastos Extra ($)</label>
            <input type="number" step="0.01" wire:model.defer="fin_gastos" class="form-control h-12 text-lg" placeholder="0.00">
            @error('fin_gastos') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Notas del Acuerdo</label>
            <input type="text" wire:model.defer="fin_notas_acuerdo" class="form-control h-12 text-lg" placeholder="Condiciones de pago...">
        </div>

        <div class="col-span-12 md:col-span-2 flex justify-end">
            <button class="btn btn-primary text-lg px-8 py-2.5 w-full" wire:click="saveFinanzas">
                Actualizar
            </button>
        </div>
    </div>

    {{-- BLOQUE 2: REGISTRAR NUEVO ABONO --}}
    <div class="mt-8 grid grid-cols-12 gap-6 items-end bg-slate-50 p-6 rounded-lg border border-slate-200">
        <div class="col-span-12">
            <h3 class="text-lg font-bold">Registrar Nuevo Abono</h3>
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">¿Qué cliente pagó?</label>
            <select wire:model.defer="pago_customer_id" class="form-select h-12 text-lg">
                <option value="">Seleccione el cliente...</option>
                @if(isset($juicio))
                    @foreach($juicio->actores as $actor)
                        <option value="{{ $actor->id }}">{{ $actor->businame }} (Actor)</option>
                    @endforeach
                    @foreach($juicio->demandados as $demandado)
                        <option value="{{ $demandado->id }}">{{ $demandado->businame }} (Demandado)</option>
                    @endforeach
                @endif
            </select>
            @error('pago_customer_id') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-2">
            <label class="form-label text-base">Monto ($)</label>
            <input type="number" step="0.01" wire:model.defer="pago_monto" class="form-control h-12 text-lg">
            @error('pago_monto') <x-alert msg="{{ $message }}" /> @enderror
        </div>
        
        <div class="col-span-12 md:col-span-2">
            <label class="form-label text-base">Fecha de Pago</label>
            <input type="date" wire:model.defer="pago_fecha" class="form-control h-12 text-lg">
            @error('pago_fecha') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Comprobante Físico (Opcional)</label>
            <input type="file" wire:model="pago_comprobante" class="form-control h-12 pt-2">
            <div wire:loading wire:target="pago_comprobante" class="text-xs text-primary mt-1">Cargando...</div>
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Método</label>
            <select wire:model.defer="pago_metodo" class="form-select h-12 text-lg">
                <option value="Transferencia">Transferencia</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Cheque">Cheque</option>
            </select>
            @error('pago_metodo') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Ref. / Transacción</label>
            <input type="text" wire:model.defer="pago_referencia" class="form-control h-12 text-lg" placeholder="N° de Transferencia">
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Notas / Observaciones</label>
            <input type="text" wire:model.defer="pago_notas" class="form-control h-12 text-lg" placeholder="Ej: Abono primera cuota">
        </div>

        <div class="col-span-12 md:col-span-2 flex justify-end">
            <button class="btn btn-primary text-lg px-8 py-2.5 w-full" wire:click="savePago" wire:loading.attr="disabled" wire:target="pago_comprobante">
                Guardar Abono
            </button>
        </div>
    </div>

    {{-- BLOQUE 3: HISTORIAL DE PAGOS --}}
    @if(isset($juicio) && $juicio->finanza && $juicio->finanza->pagos->count() > 0)
    <div class="mt-8 border-t border-gray-200 pt-8">
        <h3 class="text-xl font-bold mb-6">Historial de Abonos Registrados</h3>
        <div class="overflow-x-auto">
            <table class="table table-report">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">FECHA</th>
                        <th class="whitespace-nowrap">MÉTODO</th>
                        <th class="whitespace-nowrap text-right">MONTO</th>
                        <th class="whitespace-nowrap text-center">COMPROBANTE</th>
                        <th class="text-center whitespace-nowrap">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($juicio->finanza->pagos->sortByDesc('fecha_pago') as $pago)
                    <tr class="intro-x">
                        <td>
                            {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                            <br>
                            @if($pago->cliente)
                                <span class="text-xs font-bold text-primary">{{ $pago->cliente->businame }}</span><br>
                            @endif
                            <span class="text-xs text-slate-500">{{ $pago->notas }}</span>
                        </td>
                        <td>
                            {{ $pago->metodo_pago }}
                            @if($pago->referencia_transaccion)
                                <br><span class="text-xs text-slate-500">Ref: {{ $pago->referencia_transaccion }}</span>
                            @endif
                        </td>
                        <td class="text-right font-bold text-success">$ {{ number_format($pago->monto, 2) }}</td>
                        <td class="text-center">
                            @if($pago->comprobante_ruta)
                                <a href="{{ route('tenant.media', ['path' => $pago->comprobante_ruta]) }}" target="_blank" class="btn btn-sm btn-warning text-white flex items-center justify-center w-24 mx-auto">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> Ver
                                </a>
                            @else
                                <span class="text-slate-400 text-xs">Sin adjunto</span>
                            @endif
                        </td>
                        <td class="table-report__action">
                            <div class="flex justify-center items-center gap-2">
                                {{-- Botón Facturar / Badge Facturado --}}
                                @if($pago->cliente)
                                    @if($pago->factura_id)
                                        <span class="btn btn-sm bg-slate-200 text-slate-600 flex items-center cursor-default">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Facturado
                                        </span>
                                    @else
                                        <a href="{{ route('facturas', ['cliente_id' => $pago->customer_id, 
                                        'monto_pago' => $pago->monto, 
                                        'pago_id' => $pago->id]) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success text-white flex items-center">
                                            <i data-lucide="receipt" class="w-4 h-4 mr-1"></i> Facturar
                                        </a>
                                    @endif
                                @endif

                                {{-- Botón Eliminar --}}
                                @if(!$pago->factura_id)
                                <a class="btn btn-sm btn-danger text-white flex items-center" href="javascript:;"
                                   onclick="confirm('¿Seguro que desea eliminar este pago? Esto alterará el saldo.') || event.stopImmediatePropagation()"
                                   wire:click="destroyPago({{ $pago->id }})">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Eliminar
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>