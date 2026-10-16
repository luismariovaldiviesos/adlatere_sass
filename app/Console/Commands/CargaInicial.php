<?php

namespace App\Console\Commands;

use App\Support\XlsxReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Carga inicial por Excel (plantillas_migracion/datos_cliente.xlsx).
//   php artisan carga:inicial datos_cliente.xlsx [--validar]
// --validar: solo verifica, no escribe. Siempre idempotente por claves naturales.
class CargaInicial extends Command
{
    protected $signature = 'carga:inicial {archivo} {--validar : Solo valida sin escribir}';
    protected $description = 'Carga inicial del despacho desde plantillas Excel';

    protected $ok = 0;
    protected $fail = 0;
    protected $errores = [];
    protected $claves = [];
    protected $validar = false;
    protected $mem = []; // registro en memoria para --validar (filas del mismo archivo)

    public function handle()
    {
        $this->validar = (bool) $this->option('validar');
        $arch = $this->argument('archivo');
        if (!file_exists($arch)) {
            $arch = base_path('plantillas_migracion/' . basename($arch));
        }
        if (!file_exists($arch)) {
            $this->error("No existe el archivo: {$this->argument('archivo')}");
            return 1;
        }
        $base = basename($arch);
        $this->info(($this->validar ? '[VALIDAR] ' : '[CARGAR] ') . $base);

        try {
            $sheets = XlsxReader::sheets($arch);
        } catch (\Throwable $e) {
            $this->error('Error leyendo Excel: ' . $e->getMessage());
            return 1;
        }

        DB::beginTransaction();
        try {
            if (str_starts_with($base, 'datos_cliente')) {
                // Excel del cliente: Settings + catálogo + Usuarios + Funcionarios + Clientes + Juicios
                $this->cargaBase($sheets);
                $sheets['Datos'] = $sheets['Clientes'] ?? [];
                $this->cargaClientes($sheets);
                $sheets['Datos'] = $sheets['Juicios'] ?? [];
                $this->cargaJuicios($sheets);
            } else {
                $this->error('Nombre no reconocido (use datos_cliente.xlsx): ' . $base);
                DB::rollBack();
                return 1;
            }

            if ($this->validar) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('FALLO GENERAL: ' . $e->getMessage());
            return 1;
        }

        $this->info("OK: {$this->ok} | Rechazadas: {$this->fail}");
        foreach ($this->errores as $e) {
            $this->error($e);
        }
        if (!empty($this->claves)) {
            $this->info('--- CLAVES TEMPORALES (entregar y exigir cambio) ---');
            foreach ($this->claves as $c) $this->line($c);
        }
        $log = 'carga_' . date('Ymd_His') . '_' . pathinfo($base, PATHINFO_FILENAME) . '.log';
        file_put_contents(storage_path('logs/' . $log),
            "archivo: {$base} validar=" . ($this->validar ? 1 : 0) . PHP_EOL .
            "ok={$this->ok} fail={$this->fail}" . PHP_EOL .
            implode(PHP_EOL, $this->errores) . PHP_EOL .
            implode(PHP_EOL, $this->claves) . PHP_EOL);
        $this->info("Log: storage/logs/{$log}");
        return $this->fail > 0 ? 1 : 0;
    }

    // ---------- utilidades ----------
    protected function filas(array $sheets, string $hoja): array
    {
        $rows = $sheets[$hoja] ?? [];
        if (count($rows) < 1) return [];
        array_shift($rows); // cabecera
        return array_values(array_filter($rows, fn($r) => array_filter($r, fn($v) => $v !== null && $v !== '')));
    }

    protected function err(string $donde, $fila, string $msg)
    {
        $this->fail++;
        $this->errores[] = "{$donde} fila {$fila}: {$msg}";
    }

    protected function hit()
    {
        $this->ok++;
    }

    // Normaliza identificadores (Excel puede guardarlos como número: 0102 -> 102)
    protected function idStr($v): string
    {
        if (is_float($v)) return sprintf('%.0f', $v);
        if (is_int($v)) return (string) $v;
        return trim((string) $v);
    }

    // CI ecuatoriana = 10 dígitos, RUC = 13: recupera el cero inicial si Excel lo quitó
    protected function padId($v): string
    {
        $s = $this->idStr($v);
        if (preg_match('/^\d+$/', $s)) {
            if (strlen($s) === 9) $s = '0' . $s;
            elseif (strlen($s) === 12) $s = '0' . $s;
        }
        return $s;
    }

    protected function memorize(string $tipo, $clave)
    {
        $this->mem[$tipo][(string) $clave] = true;
    }

    protected function seen(string $tipo, $clave): bool
    {
        return isset($this->mem[$tipo][(string) $clave]);
    }

    // first() en BD o, en --validar, en lo ya visto del mismo archivo
    protected function lookup(string $model, string $field, $value, ?string $memTipo = null)
    {
        $r = $model::where($field, $value)->first();
        if (!$r && $this->validar && $memTipo && $this->seen($memTipo, $value)) {
            $r = (object) ['id' => 0];
        }
        return $r;
    }

    protected function dup(string $model, string $field, $value, ?string $memTipo = null): bool
    {
        if ($model::where($field, $value)->exists()) return true;
        return $this->validar && $memTipo && $this->seen($memTipo, $value);
    }

    // ---------- 0_base ----------
    protected function cargaBase(array $sheets)
    {
        // Settings (una fila)
        foreach ($this->filas($sheets, 'Settings') as $i => $c) {
            $n = $i + 2;
            $ruc = $this->padId($c[2] ?? '');
            if ($ruc === '') { $this->err('Settings', $n, 'ruc obligatorio'); continue; }
            $data = [
                'razonSocial' => $c[0] ?? null, 'nombreComercial' => $c[1] ?? null, 'ruc' => $ruc,
                'estab' => $c[3] ?? '001', 'ptoEmi' => $c[4] ?? '001',
                'dirMatriz' => $c[5] ?? null, 'dirEstablecimiento' => $c[6] ?? null,
                'telefono' => $c[7] ?? null, 'email' => $c[8] ?? null,
                'ambiente' => (string) ($c[9] ?? '1'), 'tipoEmision' => (string) ($c[10] ?? '1'),
                'contribuyenteEspecial' => $c[11] ?? 'NO', 'obligadoContabilidad' => $c[12] ?? 'NO',
                'logo' => $c[13] ?? 'noImage.jpg', 'leyend' => $c[14] ?? null, 'printer' => $c[15] ?? null,
                'enable_caja' => (int) ($c[16] ?? 0),
            ];
            if (!$this->validar) {
                $s = \App\Models\Setting::first();
                $s ? $s->update($data) : \App\Models\Setting::create($data);
            }
            $this->hit();
            break; // solo primera fila
        }
        // Materias
        foreach ($this->filas($sheets, 'Materias') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            if ($nombre === '') { $this->err('Materias', $n, 'nombre obligatorio'); continue; }
            if (!$this->validar) \App\Models\Materia::updateOrCreate(['nombre' => $nombre], ['unidad_id' => null]);
            $this->memorize('materia', $nombre);
            $this->hit();
        }
        // Procedimientos
        foreach ($this->filas($sheets, 'Procedimientos') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            $mat = $this->lookup(\App\Models\Materia::class, 'nombre', trim((string) ($c[1] ?? '')), 'materia');
            if ($nombre === '') { $this->err('Procedimientos', $n, 'nombre obligatorio'); continue; }
            if (!$mat) { $this->err('Procedimientos', $n, "materia inexistente: {$c[1]}"); continue; }
            if (!$this->validar) \App\Models\Procedimiento::updateOrCreate(['nombre' => $nombre, 'materia_id' => $mat->id]);
            $this->memorize('proc', $nombre);
            $this->hit();
        }
        // Asuntos
        foreach ($this->filas($sheets, 'Asuntos') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            $proc = $this->lookup(\App\Models\Procedimiento::class, 'nombre', trim((string) ($c[1] ?? '')), 'proc');
            if ($nombre === '') { $this->err('Asuntos', $n, 'nombre obligatorio'); continue; }
            if (!$proc) { $this->err('Asuntos', $n, "procedimiento inexistente: {$c[1]}"); continue; }
            if (!$this->validar) \App\Models\Asunto::updateOrCreate(['nombre' => $nombre], ['procedimiento_id' => $proc->id]);
            $this->memorize('asunto', $nombre);
            $this->hit();
        }
        // Fases
        foreach ($this->filas($sheets, 'Fases') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            if ($nombre === '') { $this->err('Fases', $n, 'nombre obligatorio'); continue; }
            if (!$this->validar) \App\Models\Fase::updateOrCreate(['nombre' => $nombre]);
            $this->memorize('fase', $nombre);
            $this->hit();
        }
        // Estados
        foreach ($this->filas($sheets, 'Estados') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            $fase = $this->lookup(\App\Models\Fase::class, 'nombre', trim((string) ($c[1] ?? '')), 'fase');
            if ($nombre === '') { $this->err('Estados', $n, 'nombre obligatorio'); continue; }
            if (!$fase) { $this->err('Estados', $n, "fase inexistente: {$c[1]}"); continue; }
            if (!$this->validar) \App\Models\EstadoProcesal::updateOrCreate(['nombre' => $nombre], ['fase_id' => $fase->id, 'descripcion' => $c[2] ?? null]);
            $this->memorize('estado', $nombre);
            $this->hit();
        }
        // Unidades
        foreach ($this->filas($sheets, 'Unidades') as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            $canton = \App\Models\Canton::where('nombre', trim((string) ($c[1] ?? '')))->first();
            if ($nombre === '') { $this->err('Unidades', $n, 'nombre obligatorio'); continue; }
            if (!$canton) { $this->err('Unidades', $n, "cantón inexistente en sistema: {$c[1]}"); continue; }
            if (!$this->validar) \App\Models\Unidad::updateOrCreate(['nombre' => $nombre], ['canton_id' => $canton->id]);
            $this->memorize('unidad', $nombre);
            $this->hit();
        }
        // Usuarios
        foreach ($this->filas($sheets, 'Usuarios') as $i => $c) {
            $n = $i + 2;
            $this->cargaUsuario($n, $c[0] ?? null, $c[1] ?? null, $c[2] ?? null, $c[3] ?? null, $c[4] ?? null);
        }
        // Funcionarios (opcional)
        $this->cargaFuncionarios($sheets, 'Funcionarios', '0_base/Funcionarios');
    }

    protected function cargaUsuario($n, $nombre, $ci, $phone, $email, $perfil)
    {
        $nombre = trim((string) $nombre); $ci = $this->padId($ci);
        $email = trim((string) $email); $perfil = trim((string) $perfil);
        if ($nombre === '' || $ci === '' || $email === '') { $this->err('Usuarios', $n, 'nombre/ci/email obligatorios'); return; }
        if (!in_array($perfil, ['Abogado', 'Asistente'])) { $this->err('Usuarios', $n, "perfil debe ser Abogado o Asistente: {$perfil}"); return; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->err('Usuarios', $n, "email inválido: {$email}"); return; }
        if ($this->dup(\App\Models\User::class, 'email', $email, 'uemail')) { $this->err('Usuarios', $n, "email ya existe: {$email}"); return; }
        if ($this->dup(\App\Models\User::class, 'ci', $ci, 'uci')) { $this->err('Usuarios', $n, "ci ya existe: {$ci}"); return; }
        if (!$this->validar) {
            $tmp = Str::random(10);
            $u = \App\Models\User::create([
                'name' => substr($nombre, 0, 50), 'ci' => $ci, 'phone' => $phone ?: '0999999999',
                'email' => $email, 'profile' => $perfil, 'status' => 'ACTIVE', 'password' => bcrypt($tmp),
            ]);
            $u->syncRoles([$perfil]);
            $this->claves[] = "{$email} / temporal: {$tmp}";
        }
        $this->memorize('uemail', $email);
        $this->memorize('uci', $ci);
        $this->hit();
    }

    // ---------- 1_clientes ----------
    protected function cargaClientes(array $sheets)
    {
        foreach ($this->filas($sheets, 'Datos') as $i => $c) {
            $n = $i + 2;
            $businame = trim((string) ($c[0] ?? ''));
            $typeidenti = strtoupper(trim((string) ($c[1] ?? '')));
            $valueidenti = $this->padId($c[2] ?? '');
            if (strlen($businame) < 3) { $this->err('Clientes', $n, 'businame min 3'); continue; }
            if ($typeidenti === '' || $valueidenti === '') { $this->err('Clientes', $n, 'typeidenti/valueidenti obligatorios'); continue; }
            $cliEmail = trim((string) ($c[4] ?? ''));
            if ($cliEmail === '') { $this->err('Clientes', $n, 'email obligatorio'); continue; }
            if ($this->dup(\App\Models\Customer::class, 'valueidenti', $valueidenti, 'cliid')
                || $this->dup(\App\Models\Customer::class, 'email', $cliEmail, 'cliemail')) {
                $this->err('Clientes', $n, "duplicado (identi o email): {$valueidenti}"); continue;
            }
            if (!$this->validar) \App\Models\Customer::create([
                'businame' => $businame, 'typeidenti' => $typeidenti, 'valueidenti' => $valueidenti,
                'address' => trim((string) ($c[3] ?? '')) ?: 's/n', 'email' => $cliEmail,
                'phone' => trim((string) ($c[5] ?? '')) ?: '-', 'notes' => trim((string) ($c[6] ?? '')) ?: '-',
            ]);
            $this->memorize('cliid', $valueidenti);
            $this->memorize('cliemail', $cliEmail);
            $this->hit();
        }
    }

    // ---------- 2_juicios ----------
    protected function cargaJuicios(array $sheets)
    {
        foreach ($this->filas($sheets, 'Datos') as $i => $c) {
            $n = $i + 2;
            $satje = trim((string) ($c[0] ?? ''));
            $asunto = $this->lookup(\App\Models\Asunto::class, 'nombre', trim((string) ($c[1] ?? '')), 'asunto');
            $unidad = $this->lookup(\App\Models\Unidad::class, 'nombre', trim((string) ($c[2] ?? '')), 'unidad');
            $estado = $this->lookup(\App\Models\EstadoProcesal::class, 'nombre', trim((string) ($c[3] ?? '')), 'estado');
            $fecha = XlsxReader::toDate($c[4] ?? null);
            $prioridad = trim((string) ($c[5] ?? '')) ?: 'Media';
            $cliId = $this->padId($c[6] ?? '');
            $contraId = $this->padId($c[8] ?? '');
            $cliente = $this->lookup(\App\Models\Customer::class, 'valueidenti', $cliId, 'cliid');
            $rolCli = strtolower(trim((string) ($c[7] ?? ''))) ?: 'actor';
            $contra = $contraId !== '' ? $this->lookup(\App\Models\Customer::class, 'valueidenti', $contraId, 'cliid') : null;
            $rolContra = strtolower(trim((string) ($c[9] ?? ''))) ?: ($contra ? ($rolCli === 'actor' ? 'demandado' : 'actor') : null);
            $abMail = trim((string) ($c[10] ?? ''));
            $abogado = \App\Models\User::where('email', $abMail)->where('profile', 'Abogado')->first();
            if (!$abogado && $this->validar && $this->seen('uemail', $abMail)) $abogado = (object) ['id' => 0];
            if ($satje === '') { $this->err('Juicios', $n, 'cod_satje obligatorio'); continue; }
            if ($this->dup(\App\Models\Juicio::class, 'cod_satje', $satje, 'satje')) { $this->err('Juicios', $n, "cod_satje duplicado: {$satje}"); continue; }
            if (!$asunto) { $this->err('Juicios', $n, "asunto inexistente: {$c[1]}"); continue; }
            if (!$unidad) { $this->err('Juicios', $n, "unidad inexistente: {$c[2]}"); continue; }
            if (!$estado) { $this->err('Juicios', $n, "estado inexistente: {$c[3]}"); continue; }
            if (!$fecha) { $this->err('Juicios', $n, "fecha_inicio inválida: {$c[4]}"); continue; }
            if (!in_array($prioridad, ['Alta', 'Media', 'Baja', 'Urgente'])) { $this->err('Juicios', $n, "prioridad inválida: {$prioridad}"); continue; }
            if (!$cliente) { $this->err('Juicios', $n, "cliente inexistente: {$cliId}"); continue; }
            if (!in_array($rolCli, ['actor', 'demandado'])) { $this->err('Juicios', $n, "rol_cliente inválido: {$rolCli}"); continue; }
            if ($contraId !== '' && !$contra) { $this->err('Juicios', $n, "contraparte inexistente: {$contraId}"); continue; }
            if (!$abogado) { $this->err('Juicios', $n, "abogado inexistente o no Abogado: {$c[10]}"); continue; }
            if (!$this->validar) {
                $j = \App\Models\Juicio::create([
                    'cod_satje' => $satje, 'asunto_id' => $asunto->id, 'unidad_id' => $unidad->id,
                    'estado_procesal_id' => $estado->id, 'fecha_inicio' => $fecha, 'prioridad' => $prioridad,
                ]);
                $j->participantes()->attach($cliente->id, ['rol' => $rolCli, 'es_cliente' => true]);
                if ($contra) $j->participantes()->attach($contra->id, ['rol' => $rolContra, 'es_cliente' => false]);
                $j->abogados()->attach($abogado->id);
                $hon = (float) ($c[11] ?? 0); $gas = (float) ($c[12] ?? 0);
                if ($hon > 0 || $gas > 0 || trim((string) ($c[13] ?? '')) !== '') {
                    \App\Models\FinanzasJuicio::create([
                        'juicio_id' => $j->id, 'honorarios_totales' => $hon,
                        'gastos_extras' => $gas, 'notas_acuerdo' => $c[13] ?? null,
                    ]);
                }
            }
            $this->memorize('satje', $satje);
            $this->hit();
        }
    }

    // ---------- 3_funcionarios / hoja Funcionarios ----------
    protected function cargaFuncionarios(array $sheets, string $hoja, string $origen = 'Funcionarios')
    {
        foreach ($this->filas($sheets, $hoja) as $i => $c) {
            $n = $i + 2;
            $nombre = trim((string) ($c[0] ?? ''));
            $cargo = trim((string) ($c[1] ?? ''));
            if (strlen($nombre) < 3) { $this->err($origen, $n, 'nombre min 3'); continue; }
            if (!in_array($cargo, ['Juez', 'Secretario', 'Ayudante', 'Citador', 'Otro'])) { $this->err($origen, $n, "cargo inválido: {$cargo}"); continue; }
            if ($this->dup(\App\Models\Funcionario::class, 'nombre', $nombre, 'func')) { $this->err($origen, $n, "duplicado: {$nombre}"); continue; }
            $email = trim((string) ($c[3] ?? ''));
            if ($email !== '' && $this->dup(\App\Models\Funcionario::class, 'email', $email, 'funcemail')) { $this->err($origen, $n, "email duplicado: {$email}"); continue; }
            $unidad = trim((string) ($c[4] ?? '')) !== '' ? $this->lookup(\App\Models\Unidad::class, 'nombre', trim((string) $c[4]), 'unidad') : null;
            if (trim((string) ($c[4] ?? '')) !== '' && !$unidad) { $this->err($origen, $n, "unidad inexistente: {$c[4]}"); continue; }
            if (!$this->validar) {
                $f = \App\Models\Funcionario::create([
                    'nombre' => $nombre, 'cargo' => $cargo, 'telefono' => trim((string) ($c[2] ?? '')),
                    'email' => $email ?: null,
                ]);
                if ($unidad) $f->unidades()->sync([$unidad->id]);
            }
            $this->memorize('func', $nombre);
            if ($email !== '') $this->memorize('funcemail', $email);
            $this->hit();
        }
    }

}
