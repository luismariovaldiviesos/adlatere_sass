<div>
    <div class="intro-y col-span-12">
        @can('ver_reprocesar')

        <div class="intro-y box">

            <h2 class="text-lg font-medium text-center text-them-1 py-4">
                {{ $componentName }}
            </h2>

            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2 p-4">
                {{-- <button onclick="openPanel('add')" class="btn btn-primary shadow-md mr-2">Agregar</button> --}}
                <div class="hidden md:block mx-auto text-gray-600">
                    --
                </div>

                <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300 ">
                        <input wire:model='search' id="search" class="form-control w-56 box pr-10  placeholder-theme-13 kioskboard" type="text" placeholder="buscar...">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0 fas fa-search"></i>
                    </div>
                </div>
            </div>



            <div class="p-5">
                <div class="preview">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="text-theme-1">
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center">SECUENCIAL</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center">CLIENTE</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center">ESTADO</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center">REPROCESAR</th>
                                    <th class="border-b-2 dark:border-dark-5 whitespace-nowrap text-center">ANULAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($xmls as $xml )
                                    <tr class=" dark:bg-dark-1 {{ $loop->index % 2> 0 ? 'bg-gray-200' : '' }}">

                                        <td class="text-center font-medium">{{ $xml->secuencial }}</td>
                                        <td class="text-center font-medium">{{ $xml->cliente}}</td>
                                        <td class="text-center font-medium">
                                            @if ($xml->estado === 'enviado')
                                                Recuperar del SRI
                                            @elseif ($xml->estado === 'firmado')
                                                Reenviar al SRI
                                            @elseif ($xml->estado === 'creado')
                                                Pendiente de firmar
                                            @else
                                                {{ ucfirst($xml->estado) }} <!-- Muestra el estado por defecto si no coincide -->
                                            @endif
                                        </td>

                                        <td class="dark:border-dark-5 text-center">
                                            @if($xml->error)
                                                <div class="mb-1 text-danger font-bold" style="max-width: 400px; white-space: normal; overflow-wrap: break-word;">
                                                    <i class="fas fa-exclamation-triangle"></i> {{ Str::limit($xml->error, 100) }}
                                                </div>
                                            @endif
                                            
                                            <div class="d-flex justify-content-center">
                                                @if($xml->secuencial)
                                                    @can('reprocesar')
                                                        @if($xml->estado !== 'devuelto' && !Str::contains(strtoupper($xml->error), 'ERROR SECUENCIAL REGISTRADO'))
                                                            <button class="btn btn-warning text-white border-0 ml-3"
                                                            wire:click.prevent="retry({{ $xml->id }})"
                                                            type="button">
                                                                @if($xml->estado === 'creado')
                                                                    <i class="fas fa-file-signature fa-2x"></i> Firmar y Enviar
                                                                @elseif($xml->estado === 'firmado')
                                                                    <i class="fas fa-paper-plane fa-2x"></i> Enviar al SRI
                                                                @elseif(in_array($xml->estado, ['enviado', 'en_proceso']))
                                                                    <i class="fas fa-cloud-download-alt fa-2x"></i> Recuperar del SRI
                                                                @elseif($xml->estado === 'no_enviado')
                                                                    <i class="fas fa-wifi fa-2x"></i> Reintentar Envío
                                                                @elseif($xml->estado === 'no_autorizado')
                                                                    <i class="fas fa-search fa-2x"></i> Verificar Autorización
                                                                @else
                                                                    <i class="fas fa-sync-alt fa-2x"></i> Reprocesar
                                                                @endif
                                                            </button>
                                                        @else
                                                            <span class="text-xs text-danger font-bold text-center">
                                                                <i class="fas fa-ban"></i> Factura Devuelta/Errónea.<br>Debe eliminar y volver a crear.
                                                            </span>
                                                        @endif
                                                    @endcan
                                                @else
                                                    <span class="text-xs text-danger font-bold">
                                                        <i class="fas fa-ban"></i> Factura mal procesada (sin secuencial).<br>No es posible reprocesar.
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="dark:border-dark-5 text-center">
                                            <div class="d-flex justify-content-center">
                                                @can('anular_factura_emitida')
                                                <button class="btn btn-danger text-white border-0 ml-3"
                                                    wire:click="confirmDelete({{ $xml->id }})"
                                                    type="button"
                                                    title="Anular Factura">
                                                <i class="fas fa-trash-alt f-2x"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-gray-200 dark:bg-dark-1">
                                        <td colspan="2">
                                            <h6 class="text-center">    NO HAY FACTURAS PENDIENTES DE REPROCESAR </h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $xmls->links() }}
                    </div>
                </div>
            </div>

            @else
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endcan

        </div>
    </div>


      <script>

window.addEventListener('swal:confirm', event => {
                Swal.fire({
                    title: '¿Estás seguro de anular la factura?',
                    text: "Esta acción no se puede deshacer.",
                    type: 'warning',  // Si tu versión no soporta 'icon', usa 'type'
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#FFC107',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.value) {  // En versiones antiguas de SweetAlert2, se usa 'value' en vez de 'isConfirmed'
                       // console.log("Emitir evento Livewire: delete, con ID:", event.detail.facturaId);
                        Livewire.emit('delete', event.detail.facturaId);
                    }
                });
            });



      </script>




</div>
