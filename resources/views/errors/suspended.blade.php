<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cuenta Suspendida</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased bg-gray-100 h-screen flex items-center justify-center">
        <div class="max-w-xl w-full bg-white shadow-xl rounded-lg overflow-hidden p-8 text-center">
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-red-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Cuenta Suspendida</h1>
            <p class="text-gray-600 mb-8 text-lg">
                El acceso a este sistema ha sido temporalmente restringido.
            </p>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8 text-left">
                <p class="text-sm text-gray-500 mb-2 font-bold uppercase tracking-wider">Motivos posibles:</p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li>Falta de pago de la suscripción.</li>
                    <li>Solicitud administrativa.</li>
                    <li>Incumplimiento de términos de servicio.</li>
                </ul>
            </div>

            <div class="space-y-4">
                <p class="text-gray-800 font-medium">
                    Por favor, póngase en contacto con el administrador para regularizar su servicio.
                </p>
                <a href="mailto:soporte@facturacion.com" class="inline-flex items-center justify-center w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Contactar Soporte
                </a>
            </div>
            
            <div class="mt-8 text-xs text-gray-400">
                &copy; {{ date('Y') }} Facturación SaaS. Todos los derechos reservados.
            </div>
        </div>
    </body>
</html>
