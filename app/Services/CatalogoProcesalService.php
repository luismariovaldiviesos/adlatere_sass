<?php

namespace App\Services;

class CatalogoProcesalService
{
    public const MENSAJE_VACIO = 'Primero carga el archivo datos_cliente (catálogo vacío: materias, asuntos, estados, unidades).';

    public function completo(): bool
    {
        return empty($this->faltantes());
    }

    public function faltantes(): array
    {
        $faltantes = [];

        if (!\App\Models\Materia::exists()) {
            $faltantes[] = 'materias';
        }

        if (!\App\Models\Asunto::exists()) {
            $faltantes[] = 'asuntos';
        }

        if (!\App\Models\EstadoProcesal::exists()) {
            $faltantes[] = 'estados';
        }

        if (!\App\Models\Unidad::exists()) {
            $faltantes[] = 'unidades';
        }

        return $faltantes;
    }
}
