<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class BandejaNotificaciones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filtro = 'todas';

    public function updatingFiltro()
    {
        $this->resetPage();
    }

    public function marcarLeida($id)
    {
        $n = auth()->user()->notifications()->where('id', $id)->first();
        if ($n && !$n->read_at) {
            $n->markAsRead();
        }
    }

    public function marcarTodas()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->dispatchBrowserEvent('noty', ['msg' => 'Notificaciones marcadas como leídas.', 'type' => 'success', 'action' => '']);
    }

    public function eliminar($id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Notificación eliminada.', 'type' => 'success', 'action' => '']);
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

    public function render()
    {
        $q = auth()->user()->notifications()->latest();

        if ($this->filtro === 'no_leidas') {
            $q->whereNull('read_at');
        } elseif (in_array($this->filtro, ['tarea', 'consulta', 'audiencia', 'juicio', 'firma'], true)) {
            $q->where('data->categoria', $this->filtro);
        }

        return view('livewire.bandeja-notificaciones', [
            'items' => $q->paginate(15),
        ])->layout('layouts.theme.app');
    }
}
