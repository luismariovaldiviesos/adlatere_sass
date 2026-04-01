<div class="intro-y col-span-12">
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">
                {{ $componentName  }} | <span class="font-normal">{{ $action }}</span>
            </h2>
        </div>

        <div class="p-5 ">
            <div class="preview">

                <div class="mt-3">
                    <div class="sm:grid grid-cols-3 gap-5">
                        <div>
                            <label  class="form-label">Nombre</label>
                            <input wire:model='name' id="name" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('name')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>

                        <div>
                            <label  class="form-label">RUC</label>
                            <input wire:model='ci' id="ci" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('ci')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>
                        <div>
                            <label  class="form-label">Teléfono</label>
                            <input wire:model='phone' id="phone" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                            @error('phone')
                                <x-alert msg="{{ $message }}" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                <div class="sm:grid grid-cols-3 gap-5">
                    <div>
                        <label  class="form-label">Email</label>
                        <input wire:model='email' id="email" type="text" class="form-control form-control-lg border-start-0 kioskboard" maxlength="250">
                        @error('email')
                            <x-alert msg="{{ $message }}" />
                        @enderror
                    </div>
                    <div class="grid grid-cols-6">
                        <div class="col-end-2 bg-amber-500">
                            <label class="form-label">Perfil</label>
                            <select wire:model.lazy='profile' class="form-select form-select-lg sm:mr-2">
                                <option value="Elegir" selected>Elegir</option>
                                @foreach ($roles as $role )
                                <option value="{{$role->name}}" >{{$role->name}}</option>
                                @endforeach
                            </select>
                            @error('profile')
                            <x-alert msg="{{ $message }}" />
                        @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-6">
                        <div class="col-end-2 bg-amber-500">
                            <label class="form-label">Estado</label>
                            <select wire:model.lazy='status' class="form-select form-select-lg sm:mr-2">
                                <option value="Elegir" selected>Elegir</option>
                                <option value="ACTIVE" selected>Activo</option>
                                <option value="LOCKED" selected>Bloqueado</option>
                            </select>
                            @error('status') <span class="text-danger er">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label  class="form-label">Password</label>
                        <input wire:model='password' id="password" type="password" data-kioskboard-type="numpad" class="form-control form-control-lg border-start-0 kioskboard" maxlength="13">
                        @error('password')
                            <x-alert msg="{{ $message }}" />
                        @enderror
                    </div>
                </div>

             <div class="mt-4">
                <div class="flex items-center justify-between">
                    <div class="relative text-gray-700">
                        <input wire:model.live="searchEspecialidad" type="text" 
                            class="form-control form-control-sm w-56 box pr-10" 
                            placeholder="Buscar especialidad o materia...">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0 fas fa-search text-gray-500"></i>
                    </div>
                </div>
                <br>
                <label class="form-label font-bold text-theme-1">Especialidades del Usuario</label>
        
                <div class="border border-gray-200 rounded-md overflow-hidden mt-2" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-report table-sm mb-0">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="whitespace-nowrap w-10">
                                    <i class="fas fa-check-square"></i>
                                </th>
                                <th class="whitespace-nowrap">MATERIA / ESPECIALIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Agrupamos las especialidades por el nombre de la materia --}}
                            @foreach($especialidades->groupBy('nombre_materia') as $materia => $items)
                                <tr class="bg-gray-50">
                                    <td colspan="2" class="font-bold text-theme-1 py-2 px-4">
                                        <i class="fas fa-gavel mr-2"></i> {{ $materia }}
                                    </td>
                                </tr>
                                
                                @foreach($items as $esp)
                                    <tr class="hover:bg-gray-100 transition duration-200">
                                        <td class="w-10 text-center border-b dark:border-dark-5">
                                            <input 
                                                type="checkbox" 
                                                wire:model="especialidadesSeleccionadas" 
                                                value="{{ $esp->id }}" 
                                                id="esp_{{ $esp->id }}"
                                                class="form-check-input"
                                            >
                                        </td>
                                        <td class="border-b dark:border-dark-5">
                                            <label for="esp_{{ $esp->id }}" class="cursor-pointer block w-full text-sm py-1">
                                                {{ $esp->nombre }}
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

            <div class="mt-2 text-right text-xs font-medium text-gray-600">
                {{ count($especialidadesSeleccionadas) }} especialidades seleccionadas
            </div>

            @error('especialidadesSeleccionadas')
                <x-alert msg="{{ $message }}" />
            @enderror
         </div>

    </div>

    <div class="mt-5">
                    <x-back />

                    <x-save />
                </div>

            </div>
        </div>

    </div>




</div>




