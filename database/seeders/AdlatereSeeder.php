<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Provincia;
use App\Models\Canton;
use App\Models\Unidad;
use App\Models\Materia;
use App\Models\Procedimiento;
use App\Models\Asunto;

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
        Unidad::updateOrCreate(['nombre' => 'GARANTIAS JURISDICCIONALES EN MATERIA CIVIL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'GARANTIAS JURISDICCIONALES EN MATERIA PENAL', 'canton_id' => 1]);
        Unidad::updateOrCreate(['nombre' => 'GARANTIAS JURISDICCIONALES EN MATERIA FAMILIA', 'canton_id' => 1]);

        //materias
        Materia::updateOrCreate(['nombre' => 'Civil', 'unidad_id' => 1]);
        Materia::updateOrCreate(['nombre' => 'Penal', 'unidad_id' => 9]);
        Materia::updateOrCreate(['nombre' => 'Familia, Niñez y Adolescencia', 'unidad_id' => 6]);
        Materia::updateOrCreate(['nombre' => 'Laboral', 'unidad_id' => 3]);
        Materia::updateOrCreate(['nombre' => 'Inquilinato', 'unidad_id' => 1]);
        Materia::updateOrCreate(['nombre' => 'Constitucional', 'unidad_id' => 25]);
        Materia::updateOrCreate(['nombre' => 'Contencioso Administrativo', 'unidad_id' => 5]);
        Materia::updateOrCreate(['nombre' => 'Contencioso Tributario', 'unidad_id' => 7]);
        Materia::updateOrCreate(['nombre' => 'Contencioso Tributario', 'unidad_id' => 7]);
        Materia::updateOrCreate(['nombre' => 'Violencia contra la Mujer y Miembros del Núcleo Familiar', 'unidad_id' => 6]);

        //procedimientos
        Procedimiento::updateOrCreate(['nombre' => 'Ordinario', 'materia_id' => 1]); // materia civil 1
        Procedimiento::updateOrCreate(['nombre' => 'Sumario', 'materia_id' => 1]);   // materia civil 2
        Procedimiento::updateOrCreate(['nombre' => 'Ejecutivo', 'materia_id' => 1]);// materia civil 3
        Procedimiento::updateOrCreate(['nombre' => 'Monitorio', 'materia_id' => 1]);// materia civil 4
        Procedimiento::updateOrCreate(['nombre' => 'Voluntario', 'materia_id' => 1]);// materia civil 5
        Procedimiento::updateOrCreate(['nombre' => 'Ejecución', 'materia_id' => 1]);// materia civil 6

        Procedimiento::updateOrCreate(['nombre' => 'Sumario', 'materia_id' => 3]);  //materia familia 7
        Procedimiento::updateOrCreate(['nombre' => 'Voluntario', 'materia_id' => 3]);  //materia familia 8
        Procedimiento::updateOrCreate(['nombre' => 'Ejecutivo', 'materia_id' => 3]);  //materia familia 9

        Procedimiento::updateOrCreate(['nombre' => 'Sumario', 'materia_id' => 4]);  //materia laboral 10
        Procedimiento::updateOrCreate(['nombre' => 'Ordinario', 'materia_id' => 4]);  //materia laboral 11
        Procedimiento::updateOrCreate(['nombre' => 'Ejecutivo', 'materia_id' => 4]);  //materia laboral 12

        Procedimiento::updateOrCreate(['nombre' => 'Inquilinato', 'materia_id' => 5]);  //materia inquilinato 13
        Procedimiento::updateOrCreate(['nombre' => 'Sumario', 'materia_id' => 5]);  //materia inquilinato 14
        Procedimiento::updateOrCreate(['nombre' => 'Monitorio', 'materia_id' => 5]);  //materia inquilinato 15


        Procedimiento::updateOrCreate(['nombre' => 'Instrucción Fiscal / Ordinario', 'materia_id' => 2]); //materia penal 16
        Procedimiento::updateOrCreate(['nombre' => 'Directo', 'materia_id' => 2]); //materia penal 17
        Procedimiento::updateOrCreate(['nombre' => 'Abreviado', 'materia_id' => 2]); //materia penal 18
        Procedimiento::updateOrCreate(['nombre' => 'Expedito', 'materia_id' => 2]); //materia penal 19
        Procedimiento::updateOrCreate(['nombre' => 'Ejercicio Privado de la Acción', 'materia_id' => 2]); //materia penal    20      
        
        Procedimiento::updateOrCreate(['nombre' => 'Acción de Protección', 'materia_id' => 6]); //materia constitucional 21
        Procedimiento::updateOrCreate(['nombre' => 'Medidas Cautelares', 'materia_id' => 6]); //materia constitucional 22
        Procedimiento::updateOrCreate(['nombre' => 'Hábeas Corpus', 'materia_id' => 6]); //materia constitucional  23

        Procedimiento::updateOrCreate(['nombre' => 'Ordinario', 'materia_id' => 7]); //contencioso administrativo 24
        Procedimiento::updateOrCreate(['nombre' => 'Sumario', 'materia_id' => 7]); //contencioso administrativo 25
        


        


        Asunto::updateOrCreate(['nombre' => 'Cobro de Letra de Cambio', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        Asunto::updateOrCreate(['nombre' => 'Cobro de Cheque', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        Asunto::updateOrCreate(['nombre' => 'Cobro de Pagaré', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        Asunto::updateOrCreate(['nombre'    => 'Cobro de Contrato de Arrendamiento', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        Asunto::updateOrCreate(['nombre' => 'Cobro de Contrato de Compra Venta', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        Asunto::updateOrCreate(['nombre' => 'Cobro de Contrato de Prestamo', 'procedimiento_id' => 3]); //procedimiento ejecutivo materia civil
        
        Asunto::updateOrCreate(['nombre' => 'Daños y Perjuicios', 'procedimiento_id' => 1]); //procedimiento ordinario  materia civil
        Asunto::updateOrCreate(['nombre'    => 'Prescripción Adquisitiva de Dominio', 'procedimiento_id' => 1]); //procedimiento ordinario  materia civil
        Asunto::updateOrCreate(['nombre' => 'Reivindicación de Bien Inmueble', 'procedimiento_id' => 1]); //procedimiento ordinario  materia civil
        Asunto::updateOrCreate(['nombre' => 'Nulidad de Contrato', 'procedimiento_id' => 1]); //procedimiento ordinario  materia civil
        
        Asunto::updateOrCreate(['nombre'    => 'Cobro de Facturas', 'procedimiento_id' => 4]); //procedimiento monitorio  materia civil
        Asunto::updateOrCreate(['nombre' => 'Cobro de Alícuotas de Condominios', 'procedimiento_id' => 4]); //procedimiento monitorio  materia civil
        Asunto::updateOrCreate(['nombre' => 'Deudas de Menor Cuantía sin Título', 'procedimiento_id' => 4]); //procedimiento monitorio  materia civil


        Asunto::updateOrCreate(['nombre' => 'Fijación de Pensión Alimenticia', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Aumento de Pensión Alimenticia', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Rebaja de Pensión Alimenticia', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Extensión  de Pensión Alimenticia', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Régimen de visitas', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Tenencia y cuidado de menores', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Divorcio Contecioso', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Autorización de Salid del País', 'procedimiento_id' => 7]); //procedimiento ejecutivo materia familia
        Asunto::updateOrCreate(['nombre' => 'Divorcio por Mutuo Acuerdo', 'procedimiento_id' => 8]); //procedimiento voluntario materia familia
        Asunto::updateOrCreate(['nombre' => 'Terminación de la Unión de Hecho por Mutuo Acuerdo', 'procedimiento_id' => 8]); //procedimiento voluntario materia familia
        Asunto::updateOrCreate(['nombre' => 'Inventarios de Bienes Sucesorios', 'procedimiento_id' => 8]); //procedimiento voluntario materia familia
        
        
        
        Asunto::updateOrCreate(['nombre' => 'Despido Intempestivo', 'procedimiento_id' => 10]); //procedimiento sumario materia laboral
        Asunto::updateOrCreate(['nombre' => 'Pago de Haberes Laborales', 'procedimiento_id' => 10]); //procedimiento sumario materia laboral
        Asunto::updateOrCreate(['nombre' => 'Visto Bueno', 'procedimiento_id' => 10]); //procedimiento sumario materia laboral
        Asunto::updateOrCreate(['nombre' => 'Accidente de Trabajo', 'procedimiento_id' => 10]); //procedimiento sumario materia laboral
        
        Asunto::updateOrCreate(['nombre' => 'Vulneración de Derechos Constitucionales', 'procedimiento_id' => 21]); //procedimiento accion de proteccion materia constitucional
        Asunto::updateOrCreate(['nombre' => 'Acción de Protección contra Acto Administrativo', 'procedimiento_id' => 21]); //procedimiento accion de proteccion materia constitucional
        Asunto::updateOrCreate(['nombre' => 'Privación Ilegal de la Libertad', 'procedimiento_id' => 23]); //procedimiento habeas corpus materia constitucional
        Asunto::updateOrCreate(['nombre' => 'Integridad Física del Detenido', 'procedimiento_id' => 23]); //procedimiento habeas corpus materia constitucional
       
       
        
        

         
        
        
        
        
    }
}
