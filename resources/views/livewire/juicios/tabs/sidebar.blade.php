<div class="intro-y box xl:sticky xl:top-24">
    <div class="p-8 border-b border-slate-200">
        <h3 class="text-xl font-bold text-slate-800">Resumen del Juicio</h3>
    </div>

    <div class="p-8 space-y-12">
        
        {{-- INFORMACIÓN PRINCIPAL --}}
        <div class="space-y-8">
            <h4 class="text-lg font-semibold text-slate-700 border-b border-slate-100 pb-3 mb-6">
                Datos Generales
            </h4>

            <div class="space-y-6 text-base">
                {{-- CÓDIGO SATJE --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="hash"></i>
                        <span class="text-slate-600 font-medium text-lg">Proceso SATJE:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">{{ $cod_satje ?? 'Borrador' }}</span>
                </div>

                {{-- MATERIA --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="gavel"></i>
                        <span class="text-slate-600 font-medium text-lg">Materia:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">{{ $juicio->asunto->procedimiento->materia->nombre ?? 'No seleccionada' }}</span>
                </div>

                {{-- JURISDICCIÓN --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="map-pin"></i>
                        <span class="text-slate-600 font-medium text-lg">Unidad Judicial:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">
                        @if(isset($juicio->unidadJudicial))
                            {{ $juicio->unidadJudicial->nombre }} ({{ $juicio->unidadJudicial->canton->nombre }})
                        @else
                            No asignada
                        @endif
                    </span>
                </div>

                {{-- ESTADO --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="activity"></i>
                        <span class="text-slate-600 font-medium text-lg">Estado Procesal:</span>
                    </div>
                    @php
                        $estadoText = $juicio->estadoProcesal->nombre ?? 'Pendiente';
                    @endphp
                    <span class="font-semibold px-4 py-2 rounded-full text-base bg-blue-100 text-blue-800 border border-blue-200 flex-1 text-center max-w-xs">
                        {{ $estadoText }}
                    </span>
                </div>
            </div>
        </div>

        @if(isset($juicio) && ($juicio->actores->count() > 0 || $juicio->demandados->count() > 0))
        {{-- SUJETOS PROCESALES --}}
        <div class="space-y-8 mt-10">
            <h4 class="text-lg font-semibold text-slate-700 border-b border-slate-100 pb-3 mb-6">
                Sujetos Procesales
            </h4>

            <div class="space-y-6 text-base">
                
                @if($juicio->actores->count() > 0)
                <div class="flex items-start py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-green-500" data-lucide="user-check"></i>
                        <span class="text-slate-600 font-medium text-lg">Actores:</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        @foreach($juicio->actores as $actor)
                            <span class="font-semibold text-slate-800 text-lg">{{ $actor->businame }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($juicio->demandados->count() > 0)
                <div class="flex items-start py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-red-500" data-lucide="user-minus"></i>
                        <span class="text-slate-600 font-medium text-lg">Demandados:</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        @foreach($juicio->demandados as $demandado)
                            <span class="font-semibold text-slate-800 text-lg">{{ $demandado->businame }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                
            </div>
        </div>
        @endif

        {{-- @if(isset($juicio) && $juicio->actividades->count() > 0)
        {{-- ACTIVIDADES DEL JUICIO 
        <div class="space-y-8 mt-10">
            <h4 class="text-lg font-semibold text-slate-700 border-b border-slate-100 pb-3 mb-6">
                Últimas Actividades
            </h4>

            <div class="space-y-4">
                @foreach($juicio->actividades->sortByDesc('fecha_actividad')->take(5) as $actividad)
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-bold text-slate-800 text-base">{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</span>
                        <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y') }}</span>
                    </div>
                    <div class="text-sm text-slate-600 italic border-l-4 border-slate-300 pl-3">
                        "{{ Str::limit(strip_tags($actividad->contenido), 120, '...') }}"
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif      --}}
    </div>
</div>
