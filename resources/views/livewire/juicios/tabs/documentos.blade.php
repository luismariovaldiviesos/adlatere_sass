<div class="p-8 space-y-6">
    
    {{-- ZONA DE SUBIDA DE DOCUMENTOS GENERALES --}}
    <div class="grid grid-cols-12 gap-6 items-end bg-slate-50 p-6 rounded-lg border border-slate-200">
        <div class="col-span-12">
            <h2 class="text-lg font-medium">Subir Documento al Expediente</h2>
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Clasificación / Etapa</label>
            <select wire:model.defer="doc_origen_tipo" class="form-select h-12 text-lg">
                <option value="General">General / Otros</option>
                <option value="Carátula / Demanda">Carátula / Demanda Inicial</option>
                <option value="Sujetos Procesales">Sujetos Procesales (Cédulas, Poderes)</option>
                <option value="Audiencia">Audiencia</option>
                <option value="Actividad">Actividad / Escrito</option>
                <option value="Finanzas">Pagos / Finanzas</option>
            </select>
            @error('doc_origen_tipo') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-3">
            <label class="form-label text-base">Nombre / Descripción</label>
            <input type="text" wire:model.defer="doc_nombre" class="form-control h-12 text-lg" placeholder="Ej: Poder Notariado">
            @error('doc_nombre') <x-alert msg="{{ $message }}" /> @enderror
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="form-label text-base">Archivo (PDF, Word, JPG)</label>
            <input type="file" wire:model="doc_archivo" class="form-control h-12 pt-2">
            @error('doc_archivo') <x-alert msg="{{ $message }}" /> @enderror
            
            <div wire:loading wire:target="doc_archivo" class="text-primary mt-2 text-sm font-medium">
                Cargando archivo...
            </div>
        </div>

        <div class="col-span-12 md:col-span-2 flex justify-end">
            <button class="btn btn-primary text-lg px-8 py-2.5 w-full" wire:click="saveDocumentoGeneral" wire:loading.attr="disabled" wire:target="doc_archivo">
                <i data-lucide="upload-cloud" class="w-5 h-5 mr-2"></i> Subir
            </button>
        </div>
    </div>

    {{-- LISTADO GLOBAL DE DOCUMENTOS (EL "DRIVE" DEL JUICIO) --}}
    <div class="mt-8 border-t border-gray-200 pt-8">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-8">
            <h3 class="text-xl font-bold mb-4 sm:mb-0">Expediente Digital del Juicio</h3>
            <div class="w-full sm:w-80 relative text-slate-500">
                <i data-lucide="search" class="w-4 h-4 absolute my-auto inset-y-0 ml-3 left-0"></i>
                <input type="text" wire:model="search_doc" class="form-control w-full pl-10" placeholder="Buscar por nombre del documento...">
            </div>
        </div>

        @if(isset($juicio) && $juicio->documentos->count() > 0)
            @php
                // Filtrar la colección de documentos si hay búsqueda
                $docs = $juicio->documentos;
                if(strlen($search_doc) > 0) {
                    $docs = $docs->filter(function($item) {
                        return stripos($item->nombre, $this->search_doc) !== false;
                    });
                }
                // Agrupar los documentos por la clasificación (origen_tipo)
                $groupedDocs = $docs->sortByDesc('created_at')->groupBy('origen_tipo');
            @endphp

            @if($groupedDocs->count() > 0)
                <div class="space-y-4">
                    @foreach($groupedDocs as $tipo => $documentosGrupo)
                        {{-- ACORDEÓN NATIVO HTML PARA CADA GRUPO --}}
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
                                            <th class="whitespace-nowrap">TIPO</th>
                                            <th class="whitespace-nowrap">FECHA</th>
                                            <th class="whitespace-nowrap">TAMAÑO</th>
                                            <th class="text-center whitespace-nowrap">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentosGrupo as $doc)
                                        <tr class="intro-x">
                                            <td class="font-medium whitespace-nowrap">
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
                                                    {{ $doc->nombre }}
                                                </div>
                                            </td>
                                            <td><span class="uppercase text-xs font-bold text-slate-500">{{ $doc->tipo_archivo }}</span></td>
                                            <td>{{ $doc->created_at->format('d M Y') }}</td>
                                            <td class="text-slate-500">{{ number_format($doc->peso_kb / 1024, 2) }} MB</td>
                                            <td class="table-report__action w-56">
                                                <div class="flex justify-center items-center gap-4">
                                                    <a class="btn btn-sm btn-warning text-white flex items-center" href="{{ route('tenant.media', ['path' => $doc->ruta_archivo]) }}" target="_blank">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> Ver
                                                    </a>
                                                    <a class="btn btn-sm btn-danger text-white flex items-center" href="javascript:;"
                                                       onclick="confirm('¿Seguro que desea eliminar este documento?') || event.stopImmediatePropagation()"
                                                       wire:click="destroyDocumento({{ $doc->id }})">
                                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Eliminar
                                                    </a>
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