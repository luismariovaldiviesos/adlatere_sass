<div class="intro-y col-span-12">
    <div class="intro-y box overflow-hidden">
        <div style="padding: 34px 28px; background: linear-gradient(135deg, #0d2450, #173b99); color:#fff;">
            <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#bfd0f3;">
                Facturación electrónica
            </div>
            <h2 style="margin:8px 0 6px;font-size:25px;font-weight:800;letter-spacing:-.03em;line-height:1.2;">
                No se puede facturar sin un certificado válido
            </h2>
            <p style="margin:0;color:#c8d8f3;font-size:13px;">
                El certificado de firma electrónica es necesario para emitir y autorizar comprobantes.
            </p>
        </div>

        <div style="padding: 28px;">
            <div style="padding:14px 16px;border-radius:10px;background:#fff7e6;border:1px solid #f5dfb0;color:#7a5200;font-size:13px;">
                <strong>Validación del sistema:</strong> {{ $mensaje }}
            </div>

            <div style="margin-top:20px;display:flex;flex-wrap:wrap;gap:10px;">
                <a href="{{ route('settings') }}" class="btn btn-primary text-white">
                    <i class="fas fa-certificate mr-1"></i> Configurar certificado
                </a>
                <a href="{{ url('dash') }}" class="btn btn-outline-secondary">
                    Volver al dashboard
                </a>
            </div>

            <p style="margin:18px 0 0;color:#8a95a8;font-size:11px;line-height:1.6;">
                Carga el archivo .p12 y su contraseña en Configuración → Empresa → Firma electrónica.
                Cuando el certificado sea válido, esta pantalla dejará de bloquear la facturación.
            </p>
        </div>
    </div>
</div>
