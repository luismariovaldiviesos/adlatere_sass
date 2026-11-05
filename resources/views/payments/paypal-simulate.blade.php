<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago simulado · Adlatere SaaS</title>
    <style>
        :root { --brand:#173b99; --ink:#12213d; --muted:#66748b; --line:#e1e7f1; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            background: radial-gradient(circle at 15% 20%, #e9f1ff, transparent 40%), #f5f8fd;
            font-family: "Inter", "Segoe UI", system-ui, sans-serif; color: var(--ink);
        }
        .card {
            width: min(480px, 92vw); background: #fff; border: 1px solid var(--line);
            border-radius: 18px; box-shadow: 0 24px 60px rgba(15, 33, 68, .13); overflow: hidden;
        }
        .head { padding: 24px 28px; background: linear-gradient(135deg, #0d2450, #173b99); color: #fff; }
        .head small { display: block; color: #bbcef1; font-size: 10px; letter-spacing: .13em; text-transform: uppercase; font-weight: 700; }
        .head h1 { margin: 8px 0 0; font-size: 21px; letter-spacing: -.02em; }
        .body { padding: 25px 28px 28px; }
        .plan { background: #f4f8ff; border: 1px solid #e0ebfb; border-radius: 10px; padding: 13px 15px; font-size: 13px; color: #516383; }
        .plan strong { color: #173b99; }
        .amount { font-size: 38px; font-weight: 800; letter-spacing: -.05em; margin: 20px 0 4px; }
        .amount span { font-size: 13px; color: var(--muted); font-weight: 500; letter-spacing: 0; }
        .note { color: var(--muted); font-size: 12px; margin: 0 0 20px; }
        .actions { display: flex; gap: 9px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; flex: 1;
            border: 0; border-radius: 9px; padding: 11px 14px;
            font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none;
        }
        .btn-primary { background: #22c55e; color: #06130a; }
        .btn-primary:hover { filter: brightness(1.04); }
        .btn-secondary { background: #eef2f9; color: #3d4c65; }
        .badge { display: inline-block; margin-top: 17px; padding: 5px 9px; border-radius: 99px; background: #fff4dc; color: #805b00; font-size: 10px; font-weight: 700; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #edf0f6; color: #8a95a8; font-size: 11px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <small>Adlatere SaaS</small>
            <h1>Pago de suscripción</h1>
        </div>
        <div class="body">
            <div class="plan">
                Despacho: <strong>{{ $tenant->name }}</strong><br>
                Plan: <strong>{{ $tenant->suscription_type }}</strong>
            </div>

            <div class="amount">
                ${{ number_format($amount, 2) }} <span>USD</span>
            </div>
            <p class="note">Estás en modo de pago simulado. No se realizará ningún cargo real con PayPal.</p>

            <form method="POST" action="{{ route('payment.paypal.simulate.approve', ['domain' => request()->getHost()]) }}">
                @csrf
                <input type="hidden" name="reference" value="{{ $reference }}">
                <div class="actions">
                    <a class="btn btn-secondary" href="{{ url('/') }}">Cancelar</a>
                    <button class="btn btn-primary" type="submit">Pagar simulado</button>
                </div>
            </form>

            <span class="badge">MODO SIMULACIÓN</span>
            <div class="footer">Puedes desactivar la simulación configurando PAYPAL_MODE=sandbox y las credenciales de PayPal.</div>
        </div>
    </div>
</body>
</html>
