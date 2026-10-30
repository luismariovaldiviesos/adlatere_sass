<div class="p-8 space-y-6">

    {{-- ZONA DE SUBIDA DE DOCUMENTOS DEL EXPEDIENTE --}}
    @if(($puedeTramitar ?? false) && !$doc_modal)
    <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
        <div class="mb-4">
            <h2 class="text-lg font-medium">Subir documento al expediente</h2>
            <p class="text-sm text-slate-500 mt-1">
                Elija qué documento es y a qué parte del juicio pertenece. El nombre ya no necesita identificar la tarea.
            </p>
        </div>

        @include('livewire.juicios.tabs.documentos-form')
    </div>
    @endif

    {{-- LISTADO GLOBAL DE DOCUMENTOS (EL "DRIVE" DEL JUICIO) --}}
    <div class="border-t border-gray-200 pt-8">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
            <div>
                <h3 class="text-xl font-bold">Expediente digital del juicio</h3>
                <p class="text-sm text-slate-500 mt-1">Un documento puede estar vinculado a una actividad, audiencia, tarea o consulta.</p>
            </div>
            <div class="w-full sm:w-80 relative text-slate-500">
                <i data-lucide="search" class="w-4 h-4 absolute my-auto inset-y-0 ml-3 left-0"></i>
                <input type="text" wire:model="search_doc" class="form-control w-full pl-10" placeholder="Buscar por nombre o vínculo...">
            </div>
        </div>

        @if(isset($juicio) && $juicio->documentos->count() > 0)
            @php
                $docs = $juicio->documentos->filter(function ($item) {
                    return $item->vinculos->isEmpty()
                        || $item->vinculos->contains(fn ($vinculo) => $vinculo->vinculable_type !== 'tarea');
                });

                if (strlen(trim($search_doc)) > 0) {
                    $busqueda = mb_strtolower(trim($search_doc));
                    $docs = $docs->filter(function ($item) use ($busqueda) {
                        return str_contains(mb_strtolower($item->nombre ?? ''), $busqueda)
                            || str_contains(mb_strtolower($item->descripcion ?? ''), $busqueda)
                            || str_contains(mb_strtolower($item->vinculado_a ?? ''), $busqueda);
                    });
                }

                if (($doc_filtro_vinculo ?? 'todos') === 'expediente') {
                    $docs = $docs->filter(fn ($item) => $item->vinculos->isEmpty());
                } elseif (($doc_filtro_vinculo ?? 'todos') === 'vinculados') {
                    $docs = $docs->filter(fn ($item) => $item->vinculos->isNotEmpty());
                } elseif (in_array(($doc_filtro_vinculo ?? 'todos'), ['actividad', 'audiencia', 'consulta'], true)) {
                    $docs = $docs->filter(function ($item) use ($doc_filtro_vinculo) {
                        return $item->vinculos->contains(fn ($vinculo) => $vinculo->vinculable_type === $doc_filtro_vinculo);
                    });
                }

                $groupedDocs = $docs->sortByDesc('created_at')
                    ->groupBy(fn ($item) => $item->tipoDocumento->nombre ?? 'Sin tipo');
            @endphp

            <div class="flex flex-wrap gap-2 mb-4">
                <select wire:model="doc_filtro_vinculo" class="form-select w-auto text-sm">
                    <option value="todos">Todos los documentos</option>
                    <option value="vinculados">Solo vinculados</option>
                    <option value="expediente">Solo del expediente</option>
                    <option value="actividad">De actividades</option>
                    <option value="audiencia">De audiencias</option>
                    <option value="consulta">De consultas</option>
                </select>
            </div>

            @if($groupedDocs->count() > 0)
                <div class="space-y-4">
                    @foreach($groupedDocs as $tipo => $documentosGrupo)
                        <details class="group bg-white border border-slate-200 rounded-lg shadow-sm" open>
                            <summary class="flex items-center justify-between p-4 font-medium cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex items-center">
                                    <i data-lucide="folder" class="w-5 h-5 mr-3 text-primary"></i>
                                    <span class="text-lg uppercase">{{ $tipo }}</span>
                                    <span class="ml-3 px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-xs">{{ $documentosGrupo->count() }}</span>
                                </div>
                                <span class="transition group-open:rotate-180">
                                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-500"></i>
                                </span>
                            </summary>

                            <div class="p-4 border-t border-slate-200 overflow-x-auto">
                                <table class="table table-report -mt-2">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap">DOCUMENTO</th>
                                            <th class="whitespace-nowrap">FORMATO</th>
                                            <th class="whitespace-nowrap">VINCULADO A</th>
                                            <th class="whitespace-nowrap">ETAPA</th>
                                            <th class="whitespace-nowrap">FECHA</th>
                                            <th class="whitespace-nowrap">TAMAÑO</th>
                                            <th class="text-center whitespace-nowrap">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentosGrupo as $doc)
                                        <tr class="intro-x">
                                            <td class="font-medium min-w-[260px]">
                                                <div class="flex items-center">
                                                    @if(in_array($doc->tipo_archivo, ['pdf']))
                                                        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-danger"></i>
                                                    @elseif(in_array($doc->tipo_archivo, ['jpg', 'png', 'jpeg']))
                                                        <i data-lucide="image" class="w-5 h-5 mr-2 text-primary"></i>
                                                    @elseif(in_array($doc->tipo_archivo, ['doc', 'docx']))
                                                        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-600"></i>
                                                    @else
                                                        <i data-lucide="file" class="w-5 h-5 mr-2 text-slate-500"></i>
                                                    @endif
                                                    <div>
                                                        <div>{{ $doc->nombre }}</div>
                                                        @if($doc->descripcion)
                                                            <div class="text-xs text-slate-500 font-normal mt-0.5">{{ \Illuminate\Support\Str::limit($doc->descripcion, 80) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="uppercase text-xs font-bold text-slate-500">{{ $doc->tipo_archivo }}</span></td>
                                            <td class="text-sm text-slate-600 min-w-[220px]">{{ $doc->vinculado_a }}</td>
                                            <td class="text-sm text-slate-600">{{ $doc->estadoProcesal->nombre ?? '—' }}</td>
                                            <td>{{ $doc->created_at->format('d M Y') }}</td>
                                            <td class="text-slate-500">{{ number_format($doc->peso_kb / 1024, 2) }} MB</td>
                                            <td class="table-report__action w-56">
                                                <div class="flex justify-center items-center gap-4">
                                                    <a class="btn btn-sm btn-warning text-white flex items-center" href="{{ route('tenant.media', ['path' => $doc->ruta_archivo]) }}" target="_blank">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> Ver
                                                    </a>
                                                    @if($puedeTramitar ?? false)
                                                    <a class="btn btn-sm btn-danger text-white flex items-center" href="javascript:;"
                                                       onclick="confirm('¿Seguro que desea eliminar este documento?') || event.stopImmediatePropagation()"
                                                       wire:click="destroyDocumento({{ $doc->id }})">
                                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Eliminar
                                                    </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="alert alert-secondary show flex items-center mt-5" role="alert">
                    <i data-lucide="search-X" class="w-6 h-6 mr-2"></i> No se encontraron documentos que coincidan con la búsqueda.
                </div>
            @endif
        @else
            <div class="alert alert-secondary show flex items-center mb-2 mt-5" role="alert">
                <i data-lucide="info" class="w-6 h-6 mr-2"></i> Aún no hay documentos adjuntos a este expediente procesal.
            </div>
        @endif
    </div>
</div>
