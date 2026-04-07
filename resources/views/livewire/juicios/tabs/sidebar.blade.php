<div class="intro-y box xl:sticky xl:top-24">
    <div class="p-8 border-b border-slate-200">
        <h3 class="text-xl font-bold text-slate-800">Resumen del estudiante</h3>
    </div>

    <div class="p-8 space-y-12">
        {{-- FOTO DEL ESTUDIANTE --}}
        <div class="w-full mb-10">
            <div class="mx-auto max-w-[280px]">
                <div class="bg-slate-50 rounded-lg overflow-hidden shadow-sm border border-slate-200">
                    <div class="flex items-center justify-center aspect-[3/4]">
                        @if(isset($alumno) && $alumno->img)
                            <img src="{{ $alumno->img }}" data-action="zoom" alt="Foto del estudiante"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 p-6">
                                <i class="w-16 h-16 mb-3" data-lucide="user"></i>
                                <span class="text-sm font-medium">Sin imagen</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- INFORMACIÓN DEL ESTUDIANTE --}}
        <div class="space-y-8">
            <h4 class="text-lg font-semibold text-slate-700 border-b border-slate-100 pb-3 mb-6">
                Información personal
            </h4>

            <div class="space-y-6 text-base">
                {{-- NOMBRE --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="user"></i>
                        <span class="text-slate-600 font-medium text-lg">Nombre completo:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">{{ $nombres ?? 'No registra' }}</span>
                </div>

                {{-- CÉDULA --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="id-card"></i>
                        <span class="text-slate-600 font-medium text-lg">Cédula de identidad:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">{{ $ci ?? 'No registra' }}</span>
                </div>

                {{-- FECHA DE NACIMIENTO --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="calendar"></i>
                        <span class="text-slate-600 font-medium text-lg">Fecha de nacimiento:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">
                        @if($fecha_nacimiento)
                            {{ \Carbon\Carbon::parse($fecha_nacimiento)->locale('es')->translatedFormat('j \d\e F \d\e Y') }}
                        @else
                            No registra
                        @endif
                    </span>
                </div>

                {{-- CATEGORÍA --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="layers"></i>
                        <span class="text-slate-600 font-medium text-lg">Categoría:</span>
                    </div>
                    <span class="font-semibold text-slate-800 text-lg flex-1">{{ $alumno->categoria ?? 'No registrado' }}</span>
                </div>
            </div>
        </div>

        {{-- INFORMACIÓN ACADÉMICA --}}
        <div class="space-y-8 mt-10">
            <h4 class="text-lg font-semibold text-slate-700 border-b border-slate-100 pb-3 mb-6">
                Información académica
            </h4>

            <div class="space-y-6 text-base">
                {{-- ESTADO MATRÍCULA --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="clipboard-check"></i>
                        <span class="text-slate-600 font-medium text-lg">Estado de matrícula:</span>
                    </div>
                    @php
                        $estado = $alumno->matricula->estado ?? 'no registrado';
                        $estadoColors = [
                            'activo' => 'bg-green-100 text-green-800 border border-green-200',
                            'inactivo' => 'bg-red-100 text-red-800 border border-red-200',
                            'pendiente' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                            'no registrado' => 'bg-slate-100 text-slate-600 border border-slate-200'
                        ];
                        $colorClass = $estadoColors[strtolower($estado)] ?? $estadoColors['no registrado'];
                    @endphp
                    <span class="font-semibold px-4 py-2 rounded-full text-base {{ $colorClass }} flex-1 text-center max-w-xs">
                        {{ ucfirst($estado) }}
                    </span>
                </div>

                {{-- COSTO MENSUAL --}}
                <div class="flex items-center py-4 px-5 bg-white rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors shadow-sm">
                    <div class="flex items-center space-x-4 w-64 min-w-64">
                        <i class="w-5 h-5 text-slate-400" data-lucide="dollar-sign"></i>
                        <span class="text-slate-600 font-medium text-lg">Costo mensual:</span>
                    </div>
                    @if(isset($alumno) && isset($alumno->matricula->costo_mensual) && is_numeric($alumno->matricula->costo_mensual))
                        <span class="font-bold text-green-600 text-xl flex-1">${{ number_format($alumno->matricula->costo_mensual, 2) }}</span>
                    @else
                        <span class="font-medium text-slate-500 text-lg flex-1">No registrado</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
