<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LocalHostsService
{
    public function ensure(string $domain): array
    {
        $domain = trim(strtolower($domain));
        $entry = '127.0.0.1 ' . $domain;

        // Si ya resuelve, no hay nada que hacer (hosts o DNS wildcard).
        if (gethostbyname($domain) !== $domain) {
            return $this->result(true, $domain, $entry);
        }

        $hostsPath = $this->hostsPath();

        if (!File::exists($hostsPath)) {
            return $this->result(false, $domain, $entry, $hostsPath);
        }

        $contents = File::get($hostsPath);
        if (preg_match('/^\s*127\.0\.0\.1\s+' . preg_quote($domain, '/') . '\s*$/mi', $contents)) {
            return $this->result(true, $domain, $entry, $hostsPath);
        }

        if (is_writable($hostsPath)) {
            $prefix = str_ends_with($contents, PHP_EOL) ? '' : PHP_EOL;
            $written = @file_put_contents($hostsPath, $prefix . $entry . PHP_EOL, FILE_APPEND);

            if ($written !== false) {
                $this->flushDns();
                return $this->result(true, $domain, $entry, $hostsPath);
            }
        }

        return $this->result(false, $domain, $entry, $hostsPath);
    }

    public function scriptFor(string $domain): string
    {
        $domain = trim(strtolower($domain));

        return "@echo off\r\n"
            . "set HOSTS=%SystemRoot%\\System32\\drivers\\etc\\hosts\r\n"
            . "net session >nul 2>&1\r\n"
            . "if errorlevel 1 (\r\n"
            . "  echo Debes ejecutar este archivo como Administrador.\r\n"
            . "  pause\r\n"
            . "  exit /b 1\r\n"
            . ")\r\n"
            . "findstr /C:\"127.0.0.1 {$domain}\" \"%HOSTS%\" >nul\r\n"
            . "if errorlevel 1 (echo 127.0.0.1 {$domain}>>\"%HOSTS%\")\r\n"
            . "ipconfig /flushdns >nul\r\n"
            . "echo Dominio agregado: {$domain}\r\n"
            . "pause\r\n";
    }

    private function hostsPath(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $root = getenv('SystemRoot') ?: 'C:\\Windows';
            return $root . '\\System32\\drivers\\etc\\hosts';
        }

        return '/etc/hosts';
    }

    private function flushDns(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            @exec('ipconfig /flushdns');
        }
    }

    private function result(bool $ok, string $domain, string $entry, ?string $hostsPath = null): array
    {
        return [
            'ok' => $ok,
            'domain' => $domain,
            'entry' => $entry,
            'hosts_path' => $hostsPath ?: $this->hostsPath(),
        ];
    }
}
