<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Bloques de permisos por perfil. Re-ejecutable: deja los sets exactos
// (volver a correrlo restaura estos valores y borra ajustes manuales).
class PerfilesSeeder extends Seeder
{
    // Bloque legible => permisos que contiene
    public const BLOQUES = [
        'BASE' => ['menu_dashboard', 'ver_dash'],
        'SECCION_FACTURACION' => ['menu_facturacion'],
        // Abogado: TODO en juicios (crear, carátula, sujetos, actividades+firma,
        // audiencias, documentos, finanzas, funcionarios y abogados del juicio).
        // El código además exige: ver solo asignados, tramitar solo patrocinador,
        // archivar solo dueño, restaurar/eliminar físico solo Admin.
        'JUICIOS_ABOGADO' => [
            'menu_juicios', 'agregar_juicio', 'editar_juicio', 'eliminar_juicio',
            'ver_pestana_caratula', 'ver_pestana_sujetos', 'ver_pestana_audiencias',
            'ver_pestana_documentos', 'ver_pestana_funcionarios',
            'ver_pestana_abogados', 'actualizar_caratula',
            'agregar_sujeto', 'editar_sujeto', 'eliminar_sujeto',
            'agregar_actividad', 'editar_actividad', 'eliminar_actividad', 'firmar_actividad',
            'agregar_audiencia', 'editar_audiencia', 'eliminar_audiencia',
            'registrar_documento', 'ver_documento', 'eliminar_documento',
            'asignar_funcionario', 'eliminar_funcionario',
            'asignar_abogado', 'eliminar_abogado',
        ],
        // atender: solo Abogado (el código exige además que sea el asignado)
        'MIS_CONSULTAS' => ['menu_mis_consultas', 'guardar_notas_consulta', 'atender_consulta'],
        'CLIENTES_LECTURA' => ['menu_personas', 'menu_clientes', 'ver_cliente'],
        // facturar: solo Asistente (convertir/eliminar quedan solo en Admin vía sync total)
        // Asistente puede crear juicios (agregar_juicio) pero no editar/eliminar (solo Abogado/Admin)
        'RECEPCION' => [
            'menu_facturar', 'menu_consultas', 'ver_facturacion', 'crear_consulta', 
            'editar_datos_consulta', 'marcar_pagada_consulta', 'facturar_consulta',
            'menu_finanzas_juicios', 'crear_finanza', 'guarda_abono_finanza', 
            'facturar_abono_finanza', 'eliminar_abono_finanza',
            'menu_juicios', 'agregar_juicio', 'menu_reprocesar', 
            'menu_facturas_emitidas', 'menu_faturas_anuladas', 'menu_notas_credito',
            'menu_cajas', 'menu_arqueos',
            'menu_catalogo', 'menu_configuracion',
            'menu_empresa', 'menu_roles', 'menu_asginar_permisos'
        ],
        'CLIENTES_GESTION' => ['menu_personas', 'menu_clientes', 'ver_cliente', 'crear_cliente'],
        'CAJA_DIA' => ['menu_ventas_diarias', 'ver_venta_diaria'],
        'TAREAS_GESTION' => ['menu_tareas', 'crear_tarea', 'eliminar_tarea'],
        'TAREAS_EJECUCION' => ['menu_tareas', 'completar_tarea', 'convertir_tarea'],
        'CATALOGO_COMPLETO' => [
            'menu_categorias', 'ver_categoria', 'crear_categoria', 'editar_categoria', 'eliminar_categoria',
            'menu_productos', 'ver_producto', 'crear_producto', 'editar_producto', 'eliminar_producto',
            'menu_descuentos', 'ver_descuentos', 'crear_descuento', 'editar_descuento', 'eliminar_descuentos',
            'menu_impuestos', 'ver_impuestos', 'crear_impuestos', 'editar_impuestos', 'eliminar_impuestos',
        ],
        'USUARIOS_SISTEMA' => ['menu_usuarios'],
        'GESTION_PROCESAL' => [
            'menu_materias', 'crear_materia', 'editar_materia', 'eliminar_materia',
            'menu_procedimientos', 'crear_procedimiento', 'editar_procedimiento', 'eliminar_procedimiento',
            'menu_asuntos', 'crear_asunto', 'editar_asunto', 'eliminar_asunto',
            'menu_fases_procesales', 'crear_fase_procesal', 'editar_fase_procesal', 'eliminar_fase_procesal',
            'menu_estados_procesales', 'crear_estado_procesal', 'editar_estado_procesal', 'eliminar_estado_procesal',
            'menu_especialidades', 'crear_especialidad', 'editar_especialidad', 'eliminar_especialidad',
            'menu_tipos_actividades', 'crear_tipo_actividad', 'editar_tipo_actividad', 'eliminar_tipo_actividad',
            'menu_plantillas_tipos_actividades', 'crear_plantilla_tipo_actividad', 'editar_plantilla_tipo_actividad',
            'eliminar_plantilla_tipo_actividad', 'previsualizar_plantilla_tipo_actividad',
            'menu_prioridad_alertas', 'crear_prioridad_alerta', 'editar_prioridad_alerta', 'eliminar_prioridad_alerta'
        ],
        'SITIOS' => ['menu_provincias', 'menu_cantones', 'menu_unidades'],
    ];

    // Perfil => bloques que recibe
    public const PERFIL_BLOQUES = [
        'Abogado' => ['BASE', 'SECCION_FACTURACION', 'JUICIOS_ABOGADO', 'MIS_CONSULTAS', 'CLIENTES_LECTURA', 'TAREAS_EJECUCION'],
        'Asistente' => ['BASE', 'SECCION_FACTURACION', 'RECEPCION', 'CLIENTES_GESTION', 'CAJA_DIA', 'TAREAS_GESTION', 'CATALOGO_COMPLETO', 'USUARIOS_SISTEMA', 'GESTION_PROCESAL', 'SITIOS'],
    ];

    public static function permisosDe(array $bloques): array
    {
        $nombres = [];
        foreach ($bloques as $b) {
            foreach (self::BLOQUES[$b] ?? [] as $p) {
                $nombres[] = $p;
            }
        }
        // Solo los que existen en esta base (tolerante entre ambientes)
        return Permission::whereIn('name', array_unique($nombres))->pluck('name')->toArray();
    }

    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos del módulo Tareas y Finanzas Juicios
        foreach (['menu_tareas', 'crear_tarea', 'completar_tarea', 'convertir_tarea', 'eliminar_tarea', 'menu_finanzas_juicios'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'Admin']);
        $abogado = Role::firstOrCreate(['name' => 'Abogado']);
        $asistente = Role::firstOrCreate(['name' => 'Asistente']);

        $abogado->syncPermissions(self::permisosDe(self::PERFIL_BLOQUES['Abogado']));
        $asistente->syncPermissions(self::permisosDe(self::PERFIL_BLOQUES['Asistente']));
        Role::findByName('Admin')->syncPermissions(Permission::all());

        // Alinear el rol de cada usuario con su profile (solo si el rol existe)
        foreach (User::all(['id', 'profile']) as $u) {
            if ($u->profile && Role::where('name', $u->profile)->exists()) {
                $u->syncRoles([$u->profile]);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
