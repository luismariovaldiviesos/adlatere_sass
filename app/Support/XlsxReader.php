<?php

namespace App\Support;

// Lector mínimo de .xlsx (hojas por nombre) sin dependencias:
// solo valores (strings compartidos, inline y numéricos). Sin estilos ni fórmulas.
// Respeta la referencia de celda (r="K2") para no correr columnas con celdas vacías.
class XlsxReader
{
    // "K2" -> índice de columna 0-based (A=0). Sin referencia -> $fallback.
    public static function colIndex(?string $ref, int $fallback): int
    {
        if (!$ref || !preg_match('/^([A-Z]+)/', $ref, $m)) return $fallback;
        $idx = 0;
        foreach (str_split($m[1]) as $ch) {
            $idx = $idx * 26 + (ord($ch) - 64);
        }
        return $idx - 1;
    }

    public static function sheets(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("No se pudo abrir: {$path}");
        }
        $get = function ($name) use ($zip) {
            $i = $zip->locateName($name);
            return $i === false ? null : $zip->getFromIndex($i);
        };

        $shared = [];
        if ($xml = $get('xl/sharedStrings.xml')) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->xpath('//*[local-name()="si"]') as $si) {
                $t = '';
                foreach ($si->xpath('.//*[local-name()="t"]') as $n) {
                    $t .= (string) $n;
                }
                $shared[] = $t;
            }
        }

        $wb = $get('xl/workbook.xml');
        $rels = $get('xl/_rels/workbook.xml.rels');
        $targets = [];
        if ($rels) {
            $rx = simplexml_load_string($rels);
            foreach ($rx->xpath('//*[local-name()="Relationship"]') as $rel) {
                $a = $rel->attributes();
                $targets[(string) $a['Id']] = (string) $a['Target'];
            }
        }
        $order = [];
        if ($wb) {
            $wx = simplexml_load_string($wb);
            foreach ($wx->xpath('//*[local-name()="sheet"]') as $sh) {
                $a = $sh->attributes();
                $ns = $sh->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $rid = $ns ? (string) ($ns['id'] ?? '') : '';
                $order[(string) $a['name']] = $targets[$rid] ?? null;
            }
        }

        $out = [];
        foreach ($order as $name => $target) {
            if (!$target) continue;
            $file = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/' . $target;
            $xml = $get($file);
            if (!$xml) continue;
            $sx = simplexml_load_string($xml);
            $rows = [];
            foreach ($sx->xpath('//*[local-name()="row"]') as $row) {
                $cells = [];
                foreach ($row->xpath('./*[local-name()="c"]') as $c) {
                    $a = $c->attributes();
                    $ref = isset($a['r']) ? (string) $a['r'] : null;
                    $idx = self::colIndex($ref, count($cells));
                    while (count($cells) < $idx) {
                        $cells[] = null;
                    }
                    $t = isset($a['t']) ? (string) $a['t'] : 'n';
                    $v = null;
                    $vn = $c->xpath('./*[local-name()="v"]');
                    if ($t === 'inlineStr') {
                        $tt = $c->xpath('.//*[local-name()="t"]');
                        $v = $tt ? (string) $tt[0] : null;
                    } elseif ($t === 's') {
                        $v = $vn ? ($shared[(int) (string) $vn[0]] ?? null) : null;
                    } else {
                        $v = $vn ? (string) $vn[0] : null;
                        if ($v !== null && $v !== '' && is_numeric($v)) {
                            $v = $v + 0;
                        }
                    }
                    if (is_string($v)) {
                        $v = trim($v);
                        if ($v === '') $v = null;
                    }
                    $cells[$idx] = $v;
                }
                ksort($cells);
                $rows[] = array_values($cells);
            }
            $out[$name] = $rows;
        }
        $zip->close();
        return $out;
    }

    // Convierte serial Excel o string a Y-m-d (o null si vacío)
    public static function toDate($v): ?string
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) {
            $ts = ((float) $v - 25569) * 86400;
            return gmdate('Y-m-d', (int) $ts);
        }
        $s = trim((string) $v);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s)) return substr($s, 0, 10);
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $s, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        $ts = strtotime($s);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    public static function toDateTime($v): ?string
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) {
            $ts = ((float) $v - 25569) * 86400;
            return gmdate('Y-m-d H:i', (int) $ts);
        }
        $s = trim((string) $v);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})(?:[ T](\d{2}:\d{2})(?::\d{2})?)?/', $s, $m)) {
            return $m[1] . ' ' . ($m[2] ?? '00:00');
        }
        $ts = strtotime($s);
        return $ts ? date('Y-m-d H:i', $ts) : null;
    }
}
