@can('ver_empresa')
<div class="intro-y col-span-12">
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">
               Datos Empresa
           </h2>

       </div>
       <div id="vertical-form" class="p-5">
        <div class="preview grid grid-cols-12 gap-5">

            <div class="col-span-4">
                <label  class="form-label">Razon Social</label>
                <input wire:model="razonSocial"  id="razonSocial" type="text"
                class="form-control  kioskboard"  placeholder="" />
                @error('razonSocial')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-4">
                <label  class="form-label">Nombre del negocio</label>
                <input wire:model="nombreComercial"  id="nombreComercial" type="text"
                class="form-control  kioskboard"  placeholder="" />
                @error('nombreComercial')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-2">
                <label  class="form-label">Ruc</label>
                <input wire:model="ruc"  id="ruc" type="text"
                class="form-control  kioskboard"  placeholder="" />
                @error('ruc')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-2">
                <label  class="form-label">Establecimiento</label>
                <input wire:model="estab"  id="estab" type="text"
                class="form-control  kioskboard"  placeholder="eje: 001" />
                @error('estab')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-4">
                <label  class="form-label">Matriz</label>
                <input wire:model="dirMatriz"  id="dirMatriz" type="text"
                class="form-control  kioskboard"  placeholder="dirección" />
                @error('dirMatriz')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-4">
                <label  class="form-label">Sucursal</label>
                <input wire:model="dirEstablecimiento"  id="dirEstablecimiento" type="text"
                class="form-control  kioskboard"  placeholder="dirección" />
                @error('dirEstablecimiento')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-2">
                <label  class="form-label">Pto Emision</label>
                <input wire:model="ptoEmi"  id="ptoEmi" type="text"
                class="form-control  kioskboard"  placeholder="eje:001" />
                @error('ptoEmi')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-2">
                <label  class="form-label">telefono</label>
                <input wire:model="telefono"  id="telefono" type="text"
                class="form-control  kioskboard"  placeholder="" />
                @error('telefono')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>


            <div class="col-span-2">
                <label  class="form-label">email</label>
                <input wire:model="email"  id="email" type="text"
                class="form-control  kioskboard"  placeholder="" />
                @error('email')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-2">
                <label  class="form-label">Ambiente 1 pruebas -2 prod  </label>
                <input wire:model="ambiente"  id="ambiente" type="text"
                class="form-control  kioskboard"  placeholder="ejem1" />
                @error('ambiente')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-2">
                <label  class="form-label">tipo Emision</label>
                <input wire:model="tipoEmision"  id="tipoEmision" type="text"
                class="form-control  kioskboard"  placeholder="eje: fastFOOD" />
                @error('tipoEmision')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-2">
                <label  class="form-label">Contribuyente especial revisar</label>
                <input wire:model="contribuyenteEspecial"  id="contribuyenteEspecial" type="text"
                class="form-control  kioskboard"  placeholder="eje: 001" />
                @error('contribuyenteEspecial')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-2">
                <label  class="form-label">Conta select</label>
                <input wire:model="obligadoContabilidad"  id="obligadoContabilidad" type="text"
                class="form-control  kioskboard"  placeholder="eje: 001" />
                @error('obligadoContabilidad')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-2">
                <label  class="form-label text-theme-1">Próximo nro. de Factura</label>
                <input wire:model="secuencial_factura"  id="secuencial_factura" type="number"
                class="form-control  kioskboard border-theme-1"  placeholder="eje: 101" />
                @error('secuencial_factura')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            
             <div class="col-span-12 md:col-span-4">
                <label  class="form-label text-theme-1">Régimen RIMPE</label>
                <select wire:model="rimpe_type" class="form-control kioskboard border-theme-1">
                    <option value="Ninguno">Ninguno</option>
                    <option value="Contribuyente Régimen RIMPE">Contribuyente Régimen RIMPE (Emprendedor)</option>
                    <option value="Negocio Popular">Negocio Popular</option>
                </select>
                @error('rimpe_type')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-12 md:col-span-4">
                <label  class="form-label text-theme-1">Agente de Retención (Resolución)</label>
                <input wire:model="agente_retencion"  type="text"
                class="form-control  kioskboard border-theme-1"  placeholder="Resolución No. 1" />
                @error('agente_retencion')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-4">
                <label  class="form-label">Archivo PKC</label>
                <input wire:model="cert_file" accept=".p12" class="form-control" type="file">
                <div wire:loading wire:target="cert_file" class="text-theme-9">Subiendo archivo...</div>
                @error('cert_file')
                    <x-alert msg="{{ $message }}" />
                    @enderror
            </div>
            <div class="col-span-4">
                <label  class="form-label">Contraseña PKC</label>
                <input wire:model="cert_password"  id="cert_password" type="password"
                class="form-control  kioskboard" />
                @error('cert_password')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>

            <div class="col-span-4">
                <label  class="form-label">Imágen (Logo Tickets)</label>
                <input wire:model="logo" accept="image/x-png,image/jpeg,image/jpg" class="form-control" type="file">
                @error('logo')
                    <x-alert msg="{{ $message }}" />
                    @enderror
            </div>

            <div class="col-span-4">
                <label  class="form-label">Días para anular</label>
                <input wire:model="annulment_days"  type="text"
                class="form-control  kioskboard" />
                @error('annulment_days')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>
            <div class="col-span-4">
                <label  class="form-label">Leyenda:</label>
                <input wire:model="leyend"  id="leyend" type="text"
                class="form-control  kioskboard"  placeholder="eje: gracias por su compra" />
                @error('leyend')
                <x-alert msg="{{ $message }}" />
                @enderror
            </div>


            <div class="col-span-4 flex items-center mt-5">
                <div class="form-check form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                    <label class="form-check-label ml-0 sm:ml-2" for="show-example-1">Habilitar Control de Cajas</label>
                    <input wire:model="enable_caja" id="show-example-1" class="show-code form-check-input mr-0 ml-3" type="checkbox">
                </div>
            </div>


            <div class="col-span-12 md:col-span-3 ">
                @if($logoPreview)
                <img class="rounded-lg recent-product-img " data-action="zoom" src="{{ asset($logoPreview) }}" width="150px">
                <h5>Logo actual</h5>
                @endif

            </div>


            <div class="col-span-12 md:col-span-3 flex justify-center" >
                @if($logo)
                <div>
                    <img class="rounded-lg  recent-product-img" src="{{ $logo->temporaryUrl() }}" width="150px">
                    <h5 class="text-center">Nuevo logo</h5>
                </div>

                @endif
            </div>




            <div class="col-span-12">
                @can('editar_empresa')

                <x-save />
                @endcan

            </div>
        </div>

    </div>
</div>
</div>
@else
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>¡Lo sentimos!</strong> No tienes permisos para ver esta sección.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endcan
