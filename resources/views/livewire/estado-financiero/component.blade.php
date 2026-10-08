<div>
    <div class="intro-y col-span-12">
        <div class="intro-y box p-5">
            <h2 class="text-lg font-medium text-center py-2">Estado financiero del despacho (solo lectura)</h2>

            <div class="grid grid-cols-12 gap-4 mt-4">
                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="box p-4 text-center border-l-4 border-red-500">
                        <div class="text-xs text-gray-500 uppercase">Por cobrar</div>
                        <div class="text-2xl font-bold">${{ number_format($resumen['porCobrar'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Saldos de juicios + consultas pendientes</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="box p-4 text-center border-l-4 border-yellow-500">
                        <div class="text-xs text-gray-500 uppercase">Cobrado no facturado</div>
                        <div class="text-2xl font-bold">${{ number_format($resumen['cobradoNoFacturado'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Abonos y consultas sin factura autorizada</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="box p-4 text-center border-l-4 border-green-500">
                        <div class="text-xs text-gray-500 uppercase">Facturado autorizado</div>
                        <div class="text-2xl font-bold">${{ number_format($resumen['facturado'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Neto de notas de crédito</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="box p-4 text-center border-l-4 border-gray-400">
                        <div class="text-xs text-gray-500 uppercase">Cortesías</div>
                        <div class="text-2xl font-bold">{{ $resumen['cortesias'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Consultas no cobradas</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-4 mt-6">
                <div class="col-span-12 lg:col-span-6">
                    <div class="box p-4">
                        <h3 class="font-bold mb-2">Top saldos por juicio</h3>
                        <table class="table text-sm">
                            <thead><tr><th>Juicio</th><th class="text-right">Saldo</th></tr></thead>
                            <tbody>
                                @forelse($topDeudores as $t)
                                <tr><td>{{ $t['juicio']['cod_satje'] ?? ('#' . $t['juicio_id']) }}</td><td class="text-right">${{ number_format($t['saldo'], 2) }}</td></tr>
                                @empty<tr><td colspan="2" class="text-center text-gray-500">Sin saldos pendientes</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <div class="box p-4">
                        <h3 class="font-bold mb-2">Consultas pendientes de pago</h3>
                        <table class="table text-sm">
                            <thead><tr><th>Cliente</th><th class="text-right">Costo</th></tr></thead>
                            <tbody>
                                @forelse($consultasPendientes as $c)
                                <tr><td>{{ $c['customer']['businame'] ?? ('#' . $c['customer_id']) }}</td><td class="text-right">${{ number_format($c['costo'], 2) }}</td></tr>
                                @empty<tr><td colspan="2" class="text-center text-gray-500">Sin pendientes</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <div class="box p-4">
                        <h3 class="font-bold mb-2">Abonos cobrados sin facturar</h3>
                        <table class="table text-sm">
                            <thead><tr><th>Juicio</th><th class="text-right">Monto</th></tr></thead>
                            <tbody>
                                @forelse($cobrosNoFacturados as $p)
                                <tr><td>{{ $p['finanza']['juicio']['cod_satje'] ?? ('Pago #' . $p['id']) }} · {{ $p['fecha_pago'] }}</td><td class="text-right">${{ number_format($p['monto'], 2) }}</td></tr>
                                @empty<tr><td colspan="2" class="text-center text-gray-500">Todo facturado</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <div class="box p-4">
                        <h3 class="font-bold mb-2">Consultas cobradas sin factura autorizada</h3>
                        <table class="table text-sm">
                            <thead><tr><th>Cliente</th><th>Estado</th><th class="text-right">Costo</th></tr></thead>
                            <tbody>
                                @forelse($consultasPorFacturar as $c)
                                <tr><td>{{ $c['customer']['businame'] ?? ('#' . $c['customer_id']) }}</td><td>{{ $c['estado_pago'] }}</td><td class="text-right">${{ number_format($c['costo'], 2) }}</td></tr>
                                @empty<tr><td colspan="3" class="text-center text-gray-500">Sin pendientes</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="box p-4">
                        <h3 class="font-bold mb-2">Últimas facturas autorizadas</h3>
                        <table class="table text-sm">
                            <thead><tr><th>Secuencial</th><th>Tipo</th><th>Cliente</th><th class="text-right">Total</th></tr></thead>
                            <tbody>
                                @forelse($facturasRecientes as $f)
                                <tr><td>{{ $f['secuencial'] }}</td><td>{{ $f['codDoc'] === '04' ? 'N/C' : 'FAC' }}</td><td>{{ $f['customer']['businame'] ?? '' }}</td><td class="text-right">${{ number_format($f['total'], 2) }}</td></tr>
                                @empty<tr><td colspan="4" class="text-center text-gray-500">Sin facturas</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
