<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración Central') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Empresas Registradas</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Nombre</th>
                                    <th class="px-4 py-2">Plan</th>
                                    <th class="px-4 py-2">Dominio</th>
                                    <th class="px-4 py-2">Fecha Registro</th>
                                    <th class="px-4 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenants as $tenant)
                                <tr>
                                    <td class="border px-4 py-2">{{ $tenant->id }}</td>
                                    <td class="border px-4 py-2">{{ $tenant->name }}</td>
                                    <td class="border px-4 py-2">{{ $tenant->suscription_type }}</td>
                                    <td class="border px-4 py-2">
                                        @foreach($tenant->domains as $domain)
                                            <a href="http://{{ $domain->domain }}" target="_blank" class="text-blue-600 hover:underline">{{ $domain->domain }}</a><br>
                                        @endforeach
                                    </td>
                                    <td class="border px-4 py-2">{{ $tenant->created_at->format('d/m/Y') }}</td>
                                    <td class="border px-4 py-2">
                                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Ver</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
