<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Notificaciones extends Component
{
    public $open = false;
    public $lastCount = null;

    public function mount()
    {
        $this->lastCount = $this->unreadCount();
    }

    private function unreadCount(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        return auth()->user()->unreadNotifications()->count();
    }

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function cerrar()
    {
        $this->open = false;
    }

    public function refrescar()
    {
        $count = $this->unreadCount();

        if ($this->lastCount !== null && $count > $this->lastCount) {
            $this->dispatchBrowserEvent('noty', [
                'msg' => 'Tienes nuevas notificaciones',
                'type' => 'success',
                'action' => '',
            ]);
        }

        $this->lastCount = $count;
    }

    public function ir($id)
    {
        $n = auth()->user()->notifications()->where('id', $id)->first();
        if (!$n) {
            return;
        }

        if (!$n->read_at) {
            $n->markAsRead();
        }

        $this->open = false;

        return redirect($this->urlDestino($n->data ?? []));
    }

    private function urlDestino(array $data): string
    {
        $url = $data['url'] ?? '/';
        $perfil = auth()->user()->profile ?? null;

        if ($url === '/consultas' && in_array($perfil, ['Abogado', 'Pasante'], true)) {
            return '/mis-consultas';
        }

        return $url;
    }

    public function marcarTodas()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->lastCount = 0;
    }

    public function render()
    {
        $count = $this->unreadCount();
        $items = collect();

        if ($this->open) {
            $items = auth()->user()->notifications()->latest()->limit(10)->get();
        }

        return view('livewire.notificaciones', [
            'count' => $count,
            'items' => $items,
        ]);
    }
}
