<div>
    <div class="intro-y box p-10 text-center">
        <h2 class="text-xl font-medium text-theme-1">Aún no puedes facturar</h2>
        <p class="mt-3 text-slate-600">{{ \App\Services\EmpresaConfiguracionService::MENSAJE_INCOMPLETA }}</p>
        <a href="{{ route('settings') }}" class="btn btn-primary mt-5">Ir a Configuración de empresa</a>
    </div>
</div>
