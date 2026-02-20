<?php

namespace App\Http\Livewire;

use App\Models\Arqueo;
use App\Models\Caja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Cajas extends Component
{

    use WithPagination;

    public $nombre='', $status='elegir', $user_id=null, $selected_id = null, $search ='', $valorInicio=0;
    public $componentName = 'Cajas', $form  = false;

    public $action = 'Listado';
    protected $paginationTheme = 'tailwind';
    private $pagination = 100;


    public function render()
    {

        if (Auth()->user()->profile == 'Admin') { // si es admin veo todas las cajas del sistema
            if(strlen($this->search) > 0)
            {

                $cajas = Caja::leftJoin('users as u','u.id','cajas.user_id')
                                ->select('cajas.*','u.name as usuario')
                                ->where('cajas.nombre','like',"%{$this->search}%")
                                ->orderBy('u.name','asc')
                                ->paginate($this->pagination);
            }
            else
            {
                $cajas = Caja::leftJoin('users as u','u.id','cajas.user_id')
                ->select('cajas.*','u.name as usuario')
                ->orderBy('u.name','asc')
                ->paginate($this->pagination);
            }
        }
        else{ // si no es admin veo solo als cajas que me pertenecen
            if(strlen($this->search) > 0)
            {

                $cajas = Caja::leftJoin('users as u','u.id','cajas.user_id')
                                ->select('cajas.*','u.name as usuario')
                                ->where('cajas.nombre','like',"%{$this->search}%")
                                ->where('cajas.user_id', Auth()->user()->id)
                                ->orderBy('u.name','asc')
                                ->paginate($this->pagination);
            }
            else
            {
                $cajas = Caja::leftJoin('users as u','u.id','cajas.user_id')
                ->select('cajas.*','u.name as usuario')
                ->where('cajas.user_id', Auth()->user()->id)
                ->orderBy('u.name','asc')
                ->paginate($this->pagination);
            }

        }




        return view('livewire.cajas.component',
        [
            'cajas' => $cajas,
            'usuarios' => User::all()
        ])
        ->layout('layouts.theme.app');
    }

    public function noty($msg, $eventName = 'noty', $reset = true, $action =""){
        $this->dispatchBrowserEvent($eventName, ['msg'=>$msg, 'type' => 'success', 'action' => $action ]);
        if($reset) $this->resetUI();
    }

    public function addNew()
    {
        $this->resetUI();
        $this->form = true;
        $this->action = 'Agregar';
    }

    // Alias removed: Update blade component to use addNew() instead.

    public  function  CloseModal()
    {
        $this->resetUI();
        $this->noty(null, 'close-modal');
    }

    public  function resetUI()
    {
        $this->resetValidation();
        $this->resetPage();
        $this->nombre = '';
        $this->selected_id = null;
        $this->status = 'elegir';
        $this->user_id = null;
        $this->form = false;
        $this->action = 'Listado';
    }

    public function Edit(Caja $caja)
    {
        //dd($caja->nombre);
        $this->selected_id = $caja->id;
        $this->nombre = $caja->nombre;
        $this->status = $caja->status;
        $this->user_id = $caja->user_id;
        $this->form = true;
        $this->action = 'Editar';
    }


    public $listeners = ['resetUI','Destroy','Abrir'];

    public function Store()
    {
        $this->validate(Caja::rules($this->selected_id ?: 0), Caja::$messages);
        
        try {
            if($this->status == 'elegir')  /// es ingreso nuevo
            {
                $this->status = 0;
            }

            Caja::updateOrCreate( // crear caja
                ['id' => $this->selected_id],
                [
                    'nombre' =>  $this->nombre,
                    'status' =>  $this->status,
                    'user_id' =>  $this->user_id
                ]
            );

            $this->noty($this->selected_id > 0 ? 'Caja actualizada' : 'Caja registrada');
            $this->resetUI();
        } catch (\Exception $e) {
            \Log::error("[CAJA_STORE_ERROR] " . $e->getMessage());
            $this->noty("Error al guardar: " . $e->getMessage(), 'noty', false, 'error');
        }
    }


    // public function abrirCaja($idCaja)
    // {
    //     Arqueo::create([
    //         'caja_id' => $idCaja,
    //         'user_id' => Auth()->user()->id,
    //         'monto_inicial' => 20
    //     ]);
    //     $this->noty("arqueo iniciado");
    // }


    public function Destroy(Caja $caja)
    {
        if ($caja->arqueos->count() < 1 ) {
            $caja->delete();
            $this->noty("La caja <b>$caja->nombre</b> fue eliminada del sistema");
        }
        else{
             $this->noty("La caja no puede ser eliminada, tiene arqueos ");
        }


    }

    public function Abrir($valorInicio)
    {
        $arqueo  = Arqueo::create([
            'caja_id' => $this->selected_id,
            'user_id' => Auth()->user()->id,
            'monto_inicial' => $valorInicio
        ]);

        if($arqueo){
            Caja::where('id', $this->selected_id)
            ->update(['status' => 1]);
        }
        $this->dispatchBrowserEvent('close-modal-apertura');
        $this->noty("LA caja se abrio con éxito");
    }

    public function ResetCaja(Caja $caja)
    {
        $caja->status = 0;
        $caja->save();
        $this->noty('Caja reseteada a estado CERRADO correctmente.');
    }
}

