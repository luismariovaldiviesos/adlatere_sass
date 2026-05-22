<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Caja;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;

class InicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = Customer::create([
            'businame' => 'Consumidor final',
            'typeidenti' => 'ci',
            'valueidenti' => '0999999999',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'final@mail',
            'phone' => '999999',
            'notes' => 'consumidor final por defecto'
        ]);
         $customer = Customer::create([
            'businame' => 'Luis Mario Valdivieso',
            'typeidenti' => 'ci',
            'valueidenti' => '0104649843',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'luis.valdiviesos@funcionjudicial.gob.ec',
            'phone' => '999999',
            'notes' => 'cliente de prueba'
        ]);
         $customer = Customer::create([
            'businame' => 'Joaquin Valdivieso',
            'typeidenti' => 'ci',
            'valueidenti' => '0151377983',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'joaquin@mail.com',
            'phone' => '999999',
            'notes' => 'cliente de prueba'
        ]);
         $customer = Customer::create([
            'businame' => 'Ximena Chocho',
            'typeidenti' => 'ci',
            'valueidenti' => '0103849843',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'ximena@mail.com',
            'phone' => '999999',
            'notes' => 'cliente de prueba'
        ]);
         $customer = Customer::create([
            'businame' => 'Pedro Pablo Chocho',
            'typeidenti' => 'ci',
            'valueidenti' => '010452587',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'pablo@mail.com',
            'phone' => '999999',
            'notes' => 'cliente de prueba'
        ]);
         $customer = Customer::create([
            'businame' => 'Juan Piedra',
            'typeidenti' => 'ci',
            'valueidenti' => '0102547896',
            'address' => 'dirección',
            'address' => 'dirección',
            'email' => 'juan@mail.com',
            'phone' => '999999',
            'notes' => 'cliente de prueba'
        ]);



        $caja =  Caja::create([
            'nombre' => 'Caja Uno',
            'status' => '1',  //caja abierta
            'user_id' => '1',
        ]);

        // $categoria =  Category::create([
        //     'name' => 'Comida rápida'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Bebidas calientes'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Bebidas frías'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Cortes'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Postres'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Servicios'
        // ]);
        // $categoria =  Category::create([
        //     'name' => 'Gaseosas'
        // ]);


        // //productos
        // $product = Product::create([
        //     'category_id' => 3,
        //     'code' => 1,
        //     'name' => 'Agua',
        //     'cost' => 0.25,
        //     'price' => 0.75,
        //     'iva' => 0.00,
        //     'ice' => 0.00,
        //     'descuento' => 0.00,
        //     'price2' => 0.75,
        //     'stock' => 100,
        //     'minstock' => 10
        // ]);
        // $product = Product::create([
        //     'category_id' => 2,
        //     'code' => 2,
        //     'name' => 'Americano doble',
        //     'cost' => 1.00,
        //     'price' => 1.50,
        //     'iva' => 0.00,
        //     'ice' => 0.00,
        //     'descuento' => 0.00,
        //     'price2' => 1.50,
        //     'stock' => 100,
        //     'minstock' => 10
        // ]);

        Setting::create([
            'razonSocial' => 'EMPRESA DE PRUEBA S.A.',
            'nombreComercial' => 'MI NEGOCIO SAAS',
            'ruc' => '1799999999001',
            'estab' => '001',
            'ptoEmi' => '001',
            'dirMatriz' => 'Dirección Matriz de Prueba',
            'dirEstablecimiento' => 'Dirección Sucursal de Prueba',
            'telefono' => '0999999999',
            'email'=> 'admin@empresa.com',
            'ambiente' => '1', // 1: Pruebas, 2: Producción
            'tipoEmision' => '1',
            'contribuyenteEspecial' => 'NO',
            'obligadoContabilidad' => 'NO',
            'logo' => 'noImage.jpg',
            'leyend' => 'Gracias por su compra (Demo)',
            'printer' => 'epson',
        ]);
    }


}
