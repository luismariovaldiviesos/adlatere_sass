<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Juicio;
use App\Models\Customer;
use App\Models\Actividad;

class JuiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Juicio::create(['cod_satje'=> '001-002','asunto_id' => 1, 'unidad_id' => 1, 'estado_procesal_id'=> 1, 'fecha_inicio' => '2024-01-01']);
        Juicio::create(['cod_satje'=> '001-003','asunto_id' => 2, 'unidad_id' => 2, 'estado_procesal_id'=> 1, 'fecha_inicio' => '2024-01-01']);
        Juicio::create(['cod_satje'=> '001-004','asunto_id' => 3, 'unidad_id' => 3, 'estado_procesal_id'=> 1, 'fecha_inicio' => '2024-01-01']);

        
        // $juicio1 = Juicio::find(1);
        // $juicio1->participantes()->attach(1, ['rol' => 'actor']);
        // $juicio1->participantes()->attach(2, ['rol' => 'demandado']);
        foreach (Juicio::all() as $juicio) {
            $juicio->participantes()->attach(Customer::inRandomOrder()->first()->id, ['rol' => 'actor']);
            $juicio->participantes()->attach(Customer::inRandomOrder()->first()->id, ['rol' => 'demandado']);
        }

        Actividad::create(['juicio_id'=> 1, 'tipo_actividad_id' => 1, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 1, 'tipo_actividad_id' => 2, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 1, 'tipo_actividad_id' => 3, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 2, 'tipo_actividad_id' => 1, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 2, 'tipo_actividad_id' => 2, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 2, 'tipo_actividad_id' => 3, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 3, 'tipo_actividad_id' => 1, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 3, 'tipo_actividad_id' => 2, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);

        Actividad::create(['juicio_id'=> 3, 'tipo_actividad_id' => 3, 'user_id' => 1, 'origen' => 'Interno',
        'fecha_actividad' => '2024-01-15','descripcion' => 'Seeder', 'contenido' => 'Actividad de seeder ' ]);
    }
}
