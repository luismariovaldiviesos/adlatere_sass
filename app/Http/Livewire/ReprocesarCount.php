<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\XmlFile;

class ReprocesarCount extends Component
{
    protected $listeners = ['refreshReprocesarCount' => '$refresh'];

    public function render()
    {
        // Count directly to avoid stale cache issues
        $count = XmlFile::where('estado', '!=', 'autorizado')
            ->whereHas('factura', function($q) {
                // Ensure we don't count deleted invoices explicitly, though whereHas usually handles it
                $q->whereNull('deleted_at');
            })
            ->count();

        return view('livewire.reprocesar-count', compact('count'));
    }
}
