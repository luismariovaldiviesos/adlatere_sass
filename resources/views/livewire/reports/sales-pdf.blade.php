<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { margin-top: 20px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Usuario: {{ $user }}</p>
        <p>Fecha: {{ $startDate }} al {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Folio</th>
                <th>Cliente</th>
                <th class="text-center">Total</th>
                <th class="text-center">Pago</th>
                <th class="text-center">Items</th>
                <th class="text-center">Fecha</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td class="text-center">
                    {{ $order->secuencial }}
                    @if($order->codDoc == '04') 
                        <br><small style="color:red">(NC) Ref: {{ $order->facturaModificada->secuencial ?? '--' }}</small> 
                    @endif
                </td>
                <td>{{ $order->customer->businame ?? 'Consumidor Final' }}</td>
                <td class="text-right" style="{{ $order->codDoc == '04' ? 'color:red;' : '' }}">
                    {{ $order->codDoc == '04' ? '-' : '' }} ${{ number_format($order->total, 2) }}
                </td>
                <td class="text-center">{{ $order->paymentMethod->description ?? 'N/A' }}</td>
                <td class="text-center">{{ $order->detalles->sum('cantidad') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $order->usuario->name ?? 'Sin Usuario' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total Facturas: ${{ number_format($sumFacturas, 2) }}</p>
        <p>Total Notas Crédito: -${{ number_format($sumNotasCredito, 2) }}</p>
        <p style="border-top: 1px solid #000; padding-top: 5px;">Total Neto: ${{ number_format($totalSales, 2) }}</p>
        <p>Total Items: {{ $totalItems }}</p>
    </div>

    <div style="margin-top: 30px; width: 50%;">
        <h3>Totales por Forma de Pago</h3>
        <table>
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalByPaymentMethod as $t)
                <tr>
                    <td>{{ $t->method }}</td>
                    <td class="text-right">${{ number_format($t->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
