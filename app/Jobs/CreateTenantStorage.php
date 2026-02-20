<?php

namespace App\Jobs;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CreateTenantStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        $this->tenant->run(function () {
            $base = storage_path();
            $folders = [
                'logs',
                'app',
                'app/livewire-tmp',
                'app/public',
                'app/public/products',
                'app/public/categories',
                'app/certificados',
                'app/comprobantes',
                'app/comprobantes/autorizados',
                'app/comprobantes/pdfs',
                'app/comprobantes/xmlaprobados',
                'app/comprobantes/enviados',
                'app/comprobantes/firmados',
                'app/comprobantes/no_firmados',
                'app/comprobantes/no_autorizados',
                'app/comprobantes/devueltos',
                'app/comprobantes/no_enviados',
                'framework',
                'framework/cache',
                'framework/views',
                'framework/sessions',
            ];

            foreach ($folders as $folder) {
                $path = $base . DIRECTORY_SEPARATOR . $folder;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                @chmod($path, 0777);
            }
        });
    }
}
