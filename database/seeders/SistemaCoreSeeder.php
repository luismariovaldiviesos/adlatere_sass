<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Impuesto;
use App\Models\PrioridadAlerta;
use App\Models\Product;
use App\Models\Provincia;
use App\Models\Canton;
use App\Models\TipoActividad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Carga inicial de SISTEMA: datos iguales para todo cliente.
// 100% idempotente (firstOrCreate/updateOrCreate por claves naturales).
// NO incluye: materias/procedimientos/asuntos/fases/estados/unidades/settings/usuarios
// (van por Excel 0_base), ni juicios/clientes demo, ni planes (solo SaaS central).
class SistemaCoreSeeder extends Seeder
{
    public const PERMISOS = [
        'ver_dash','ver_categoria','crear_categoria','editar_categoria','eliminar_categoria',
        'ver_producto','crear_producto','editar_producto','eliminar_producto',
        'ver_facturacion','ver_caja','cerrar_caja','abrir_caja','editar_caja','crear_caja','eliminar_caja',
        'ver_arqueo','crear_arqueo','ver_reprocesar','reprocesar',
        'ver_facturas_emitidas','ver_detalle_factura_emitida','anular_factura_emitida',
        'reenviar_pdf_factura_emitida','descargar_archivo_factura_emitida',
        'ver_facturas_anuladas','procesar_factura_anulada',
        'ver_notas_credito','crear_nota_credito','ver_detalle_nota_credito','descargar_nota_credito',
        'ver_cliente','crear_cliente','editar_cliente','eliminar_cliente',
        'ver_reportes','ver_venta_diaria','ver_empresa','editar_empresa',
        'ver_roles','crear_roles','editar_roles','eliminar_roles',
        'ver_permisos','crear_permisos','editar_permisos','eliminar_permisos','asignar_permisos',
        'ver_usuarios','crear_usuarios','editar_usuarios','eliminar_usuarios',
        'ver_descuentos','eliminar_descuentos',
        'ver_impuestos','crear_impuestos','editar_impuestos','eliminar_impuestos',
        'ver_firma',
        'menu_dashboard','menu_productos','menu_categorias','menu_facturar','menu_cajas','menu_arqueos',
        'menu_reprocesar','menu_facturas_emitidas','menu_faturas_anuladas','menu_notas_credito',
        'menu_clientes','menu_reportes','menu_ventas_diarias','menu_configuracion','menu_empresa',
        'menu_roles','menu_asginar_permisos','menu_usuarios','menu_descuentos','menu_impuestos',
        'menu_facturacion','menu_finanzas','menu_personas','menu_catalogo',
        'menu_juicios','agregar_juicio','eliminar_juicio','editar_juicio',
        'ver_pestana_caratula','ver_pestana_sujetos','ver_pestana_audiencias','ver_pestana_documentos',
        'ver_pestana_finanzas','ver_pestana_funcionarios','ver_pestana_abogados',
        'actualizar_caratula','agregar_sujeto','editar_sujeto','eliminar_sujeto',
        'agregar_actividad','editar_actividad','eliminar_actividad','firmar_actividad',
        'agregar_audiencia','editar_audiencia','eliminar_audiencia',
        'registrar_documento','ver_documento','eliminar_documento',
        'crear_finanza','guarda_abono_finanza','facturar_abono_finanza','eliminar_abono_finanza',
        'asignar_funcionario','eliminar_funcionario','asignar_abogado','eliminar_abogado',
        'menu_consultas','crear_consulta','guardar_datos_consulta','guardar_notas_consulta',
        'editar_datos_consulta','marcar_pagada_consulta','menu_mis_consultas',
        'atender_consulta','convertir_consulta','facturar_consulta','eliminar_consulta',
        'menu_gestion_procesal',
        'menu_materias','crear_materia','editar_materia','eliminar_materia',
        'menu_procedimientos','crear_procedimiento','editar_procedimiento','eliminar_procedimiento',
        'menu_asuntos','crear_asunto','editar_asunto','eliminar_asunto',
        'menu_fases_procesales','crear_fase_procesal','editar_fase_procesal','eliminar_fase_procesal',
        'menu_estados_procesales','crear_estado_procesal','editar_estado_procesal','eliminar_estado_procesal',
        'menu_especialidades','crear_especialidad','editar_especialidad','eliminar_especialidad',
        'menu_tipos_actividades','crear_tipo_actividad','editar_tipo_actividad','eliminar_tipo_actividad',
        'menu_plantillas_tipos_actividades','crear_plantilla_tipo_actividad','editar_plantilla_tipo_actividad',
        'eliminar_plantilla_tipo_actividad','previsualizar_plantilla_tipo_actividad',
        'menu_sitios',
        'menu_provincias','menu_cantones','menu_unidades',
    ];

    public const PROVINCIAS = [
        'Azuay','Bolívar','Cañar','Carchi','Chimborazo','Cotopaxi','El Oro','Esmeraldas','Galápagos',
        'Guayas','Imbabura','Loja','Los Ríos','Manabí','Morona Santiago','Napo','Orellana','Pastaza',
        'Pichincha','Santa Elena','Santo Domingo de los Tsáchilas','Sucumbíos','Tungurahua','Zamora Chinchipe',
    ];

    // canton => provincia (nombres exactos legacy)
    public const CANTONES = [
        ['Cuenca','Azuay'],['Gualaceo','Azuay'],['Paute','Azuay'],['Sigsig','Azuay'],['Giron','Azuay'],
        ['Santa Isabel','Azuay'],['Oña','Azuay'],['Nabon','Azuay'],['Camilo Ponce Enríquez','Azuay'],['San Fernando','Azuay'],
        ['Guaranda','Bolívar'],['Azogues','Cañar'],['Tulcán','Carchi'],['Riobamba','Chimborazo'],
        ['Latacunga','Cotopaxi'],['Machala','El Oro'],['Esmeraldas','Esmeraldas'],
        ['Puerto Baquerizo Moreno','Galápagos'],['Guayaquil','Guayas'],['Ibarra','Imbabura'],
        ['Loja','Loja'],['Babahoyo','Los Ríos'],['Manta','Manabí'],['Macas','Morona Santiago'],
        ['Tena','Napo'],['Puerto Francisco de Orellana','Orellana'],['Puyo','Pastaza'],
        ['Quito','Pichincha'],['Santa Elena','Santa Elena'],['Santo Domingo','Santo Domingo de los Tsáchilas'],
    ];

    public function run()
    {
        foreach (['Admin','Abogado','Asistente'] as $rol) {
            Role::firstOrCreate(['name' => $rol]);
        }

        foreach (self::PERMISOS as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        foreach (self::PROVINCIAS as $nombre) {
            Provincia::updateOrCreate(['nombre' => $nombre]);
        }
        foreach (self::CANTONES as [$canton, $provincia]) {
            $prov = Provincia::where('nombre', $provincia)->first();
            if ($prov) {
                Canton::firstOrCreate(['nombre' => $canton], ['provincia_id' => $prov->id]);
            }
        }

        Impuesto::firstOrCreate(['nombre' => 'IVA0'], ['codigo' => '2','codigo_porcentaje' => 0,'porcentaje' => 0.00]);
        Impuesto::firstOrCreate(['nombre' => 'IVA15'], ['codigo' => '2','codigo_porcentaje' => 4,'porcentaje' => 15.00]);
        $iva0 = Impuesto::where('codigo', '2')->where('codigo_porcentaje', 0)->first();

        $cat = Category::firstOrCreate(['name' => 'Servicios']);

        $sp = Product::firstOrCreate(['code' => 'SP'], [
            'category_id' => $cat->id, 'name' => 'Honorarios de juicio',
            'cost' => 0, 'price' => 0, 'descuento' => 0, 'price2' => 0,
            'stock' => 0, 'minstock' => 0, 'es_servicio' => true,
        ]);
        if ($iva0) $sp->impuestos()->sync([$iva0->id]);

        $sc = Product::firstOrCreate(['code' => 'SERV-CONSULTA'], [
            'category_id' => $cat->id, 'name' => 'Servicio de consulta legal',
            'cost' => 0, 'price' => 0, 'descuento' => 0, 'price2' => 0,
            'stock' => 0, 'minstock' => 0, 'es_servicio' => true,
        ]);
        if ($iva0) $sc->impuestos()->syncWithoutDetaching([$iva0->id]);

        foreach (['Escrito','Providencia','Audiencia','Oficio','Contrato','Notificación','Diligencia'] as $t) {
            TipoActividad::updateOrCreate(['nombre' => $t]);
        }

        foreach ([
            ['prioridad' => 'Urgente', 'dias_verde' => 1, 'dias_amarillo' => 2, 'dias_rojo' => 3],
            ['prioridad' => 'Alta',    'dias_verde' => 2, 'dias_amarillo' => 5, 'dias_rojo' => 7],
            ['prioridad' => 'Media',   'dias_verde' => 3, 'dias_amarillo' => 10, 'dias_rojo' => 15],
            ['prioridad' => 'Baja',    'dias_verde' => 5, 'dias_amarillo' => 15, 'dias_rojo' => 25],
        ] as $r) {
            PrioridadAlerta::updateOrCreate(['prioridad' => $r['prioridad']], $r);
        }

        Customer::updateOrCreate(['valueidenti' => '0999999999'], [
            'businame' => 'Consumidor final', 'typeidenti' => 'ci', 'address' => 'dirección',
            'email' => 'final@mail', 'phone' => '999999', 'notes' => 'consumidor final por defecto',
        ]);

        $admin = User::firstOrCreate(['email' => 'admin@mail.com'], [
            'name' => 'Administrador', 'ci' => '9999999999', 'phone' => '0999999999',
            'profile' => 'Admin', 'status' => 'ACTIVE', 'password' => bcrypt('administrador'),
        ]);
        $admin->syncRoles(['Admin']);
    }
}
