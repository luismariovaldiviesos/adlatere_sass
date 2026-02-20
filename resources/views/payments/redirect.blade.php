<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Redirigiendo a Pago Seguro - Facta</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #1c3faa;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #1f2937; margin-bottom: 0.5rem; }
        p { color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>Redirigiendo a PayPhone</h2>
        <p>Por favor, no cierres esta ventana...</p>
        <p id="retry" style="display:none; margin-top: 1rem;">
            Si no eres redirigido, <a href="{{ $url }}" style="color: #1c3faa; font-weight: bold;">haz clic aquí para continuar</a>.
        </p>
    </div>

    <script type="text/javascript">
        console.log('Facta Payment Bridge - Redirecting to: {{ $url }}');
        
        // Mostrar link de reintento si tarda más de 3 segundos
        setTimeout(function() {
            document.getElementById('retry').style.display = 'block';
        }, 3000);

        // Redirigir inmediatamente via JS para establecer Referrer seguro (facta.ec)
        if ("{{ $url }}") {
            window.location.href = "{{ $url }}";
        } else {
            document.querySelector('h2').innerText = 'Error de Configuración';
            document.querySelector('p').innerText = 'No se recibió una URL válida de PayPhone.';
            console.error('Facta Error: No redirect URL provided.');
        }
    </script>
</body>
</html>
