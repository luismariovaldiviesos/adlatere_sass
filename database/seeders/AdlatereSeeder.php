<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Provincia;
use App\Models\Canton;
use App\Models\Unidad;

class AdlatereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Provincia::updateOrCreate(['nombre' => 'Azuay']);
        Provincia::updateOrCreate(['nombre' => 'Bolívar']);
        Provincia::updateOrCreate(['nombre' => 'Cañar']);
        Provincia::updateOrCreate(['nombre' => 'Carchi']);
        Provincia::updateOrCreate(['nombre' => 'Chimborazo']);
        Provincia::updateOrCreate(['nombre' => 'Cotopaxi']);
        Provincia::updateOrCreate(['nombre' => 'El Oro']);
        Provincia::updateOrCreate(['nombre' => 'Esmeraldas']);
        Provincia::updateOrCreate(['nombre' => 'Galápagos']);
        Provincia::updateOrCreate(['nombre' => 'Guayas']);
        Provincia::updateOrCreate(['nombre' => 'Imbabura']);
        Provincia::updateOrCreate(['nombre' => 'Loja']);
        Provincia::updateOrCreate(['nombre' => 'Los Ríos']);
        Provincia::updateOrCreate(['nombre' => 'Manabí']);
        Provincia::updateOrCreate(['nombre' => 'Morona Santiago']);
        Provincia::updateOrCreate(['nombre' => 'Napo']);
        Provincia::updateOrCreate(['nombre' => 'Orellana']);
        Provincia::updateOrCreate(['nombre' => 'Pastaza']);
        Provincia::updateOrCreate(['nombre' => 'Pichincha']);
        Provincia::updateOrCreate(['nombre' => 'Santa Elena']);
        Provincia::updateOrCreate(['nombre' => 'Santo Domingo de los Tsáchilas']);
        Provincia::updateOrCreate(['nombre' => 'Sucumbíos']);
        Provincia::updateOrCreate(['nombre' => 'Tungurahua']);
        Provincia::updateOrCreate(['nombre' => 'Zamora Chinchipe']);

        // cantones
        Canton::updateOrCreate(['nombre' => 'Cuenca', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Gualaceo', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Paute', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Sigsig', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Giron', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Santa Isabel', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Oña', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre'=> 'Nabon', 'provincia_id' => 1]);
        Canton::updateOrCreate(['nombre' => 'Camilo Ponce Enríquez', 'provincia_id' => 1]);        
        Canton::updateOrCreate(['nombre' => 'San Fernando', 'provincia_id' => 1]);        
        Canton::updateOrCreate(['nombre' => 'Guaranda', 'provincia_id' => 2]);
        Canton::updateOrCreate(['nombre' => 'Azogues', 'provincia_id' => 3]);
        Canton::updateOrCreate(['nombre' => 'Tulcán', 'provincia_id' => 4]);
        Canton::updateOrCreate(['nombre' => 'Riobamba', 'provincia_id' => 5]);
        Canton::updateOrCreate(['nombre' => 'Latacunga', 'provincia_id' => 6]);
        Canton::updateOrCreate(['nombre' => 'Machala', 'provincia_id' => 7]);
        Canton::updateOrCreate(['nombre' => 'Esmeraldas', 'provincia_id' => 8]);
        Canton::updateOrCreate(['nombre' => 'Puerto Baquerizo Moreno', 'provincia_id' => 9]);
        Canton::updateOrCreate(['nombre' => 'Guayaquil', 'provincia_id' => 10]);
        Canton::updateOrCreate(['nombre' => 'Ibarra', 'provincia_id' => 11]);
        Canton::updateOrCreate(['nombre' => 'Loja', 'provincia_id' => 12]);
        Canton::updateOrCreate(['nombre' => 'Babahoyo', 'provincia_id' => 13]);
        Canton::updateOrCreate(['nombre' => 'Manta', 'provincia_id' => 14]);
        Canton::updateOrCreate(['nombre' => 'Macas', 'provincia_id' => 15]);
        Canton::updateOrCreate(['nombre' => 'Tena', 'provincia_id' => 16]);
        Canton::updateOrCreate(['nombre' => 'Puerto Francisco de Orellana', 'provincia_id' => 17]);
        Canton::updateOrCreate(['nombre' => 'Puyo', 'provincia_id' => 18]);
        Canton::updateOrCreate(['nombre' => 'Quito', 'provincia_id' => 19]);
        Canton::updateOrCreate(['nombre' => 'Santa Elena', 'provincia_id' => 20]);
        Canton::updateOrCreate(['nombre' => 'Santo Domingo', 'provincia_id' => 21]);


        //unidades

        Unidad::updateOrCreate(['nombre' => 'UNIDAD CIVIL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'COACTIVAS', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE TRABAJO', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'TRIBUNAL DE GARANTÍAS PENALES', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'CONTENCIOSO ADMINISTRATIVO', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE FAMILIA, MUJER, NIÑEZ Y ADOLESCENCIA', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'TRIBUNAL DISTRITAL DE LO CONTENCIOSO TRIBUTARIO', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'TRIBUNAL DISTRITAL DE LO CONTENCIOSO ADMINISTRATIVO', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL PENAL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE VIOLENCIA CONTRA LA MUJER O MIEMBROS DEL NÚCLEO FAMILIAR', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'SALA CIVIL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'SALA PENAL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'SALA FAMILIA', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'SALA LABORAL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE GUALACEO', 'canton_id' => 2]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE PAUTE', 'canton_id' => 3]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE SIGSIG', 'canton_id' => 4]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE GIRON', 'canton_id' => 5]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE SANTA ISABEL', 'canton_id' => 6]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE OÑA', 'canton_id' => 7]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE NABON', 'canton_id' => 8]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL DE PONCE ENRIQUEZ', 'canton_id' => 9]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL ESPECIALIZADA DE GARANTIAS PENITENCIARIAS', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'UNIDAD JUDICIAL ESPECIALIZADA DE TRANSITO', 'canton_id' => 1]);
    }
}
