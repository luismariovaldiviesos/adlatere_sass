<div class="intro-y col-span-12">
    <div class="intro-y box p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-lg font-medium">Notificaciones</h2>
            <button type="button" wire:click="marcarTodas" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
            </button>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <select wire:model="filtro" class="form-select w-auto">
                <option value="todas">Todas</option>
                <option value="no_leidas">No leídas</option>
                <option value="tarea">Tareas</option>
                <option value="consulta">Consultas</option>
                <option value="audiencia">Audiencias</option>
                <option value="juicio">Casos</option>
                <option value="firma">Firmas</option>
                <option value="factura">Facturas</option>
                <option value="cobro">Cobros</option>
            </select>
        </div>

        <div class="divide-y border rounded-lg overflow-hidden">
            @forelse($items as $n)
                @php $d = $n->data; $unread = !$n->read_at; @endphp
                <div class="flex items-start gap-3 px-4 py-3 {{ $unread ? 'bg-blue-50' : 'bg-white' }}">
                    <i class="{{ $d['icono'] ?? 'fas fa-bell' }} mt-1 text-gray-500"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">{{ $d['titulo'] ?? 'Notificación' }}</p>
                        <p class="text-sm text-gray-600">{{ $d['mensaje'] ?? '' }}</p>
                        <span class="text-xs text-gray-400">{{ $n->created_at->format('d/m/Y H:i') }} · {{ $n->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($unread)
                        <button type="button" wire:click="marcarLeida('{{ $n->id }}')" class="btn btn-outline-secondary btn-sm" title="Marcar leída">
                            <i class="fas fa-check"></i>
                        </button>
                        @endif
                        <button type="button" wire:click="ir('{{ $n->id }}')" class="btn btn-primary btn-sm" title="Abrir">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" wire:click="eliminar('{{ $n->id }}')" class="btn btn-outline-danger btn-sm" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <p class="px-4 py-8 text-sm text-gray-400 text-center">No hay notificaciones.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
