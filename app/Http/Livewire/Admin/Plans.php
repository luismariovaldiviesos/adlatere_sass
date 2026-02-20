<?php

namespace App\Http\Livewire\Admin;

use App\Models\Plan;
use Livewire\Component;
use Livewire\WithPagination;

class Plans extends Component
{
    use WithPagination;

    public $search;
    public $name, $price, $description, $remote_plan_id, $invoice_limit, $planId;
    public $modalOpen = false;

    protected $rules = [
        'name' => 'required|string|min:3',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'remote_plan_id' => 'nullable|string',
        'invoice_limit' => 'nullable|integer|min:0'
    ];

    public function render()
    {
        $plans = Plan::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('price', 'asc')
            ->paginate(10);

        return view('livewire.admin.plans', ['plans' => $plans])
            ->layout('layouts.theme.app', ['title' => 'Gestión de Planes']);
    }

    public function openModal()
    {
        $this->resetInput();
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetInput();
    }

    public function resetInput()
    {
        $this->name = '';
        $this->price = '';
        $this->description = '';
        $this->remote_plan_id = '';
        $this->invoice_limit = '';
        $this->planId = null;
    }

    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        $this->planId = $id;
        $this->name = $plan->name;
        $this->price = $plan->price;
        $this->description = $plan->description;
        $this->remote_plan_id = $plan->remote_plan_id;
        $this->invoice_limit = $plan->invoice_limit;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Plan::updateOrCreate(
            ['id' => $this->planId],
            [
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'remote_plan_id' => $this->remote_plan_id,
                'invoice_limit' => $this->invoice_limit ?: null
            ]
        );

        session()->flash('message', $this->planId ? 'Plan actualizado correctamente.' : 'Plan creado correctamente.');
        $this->closeModal();
    }

    public function destroy($id)
    {
        if ($id) {
            Plan::find($id)->delete();
            session()->flash('message', 'Plan eliminado correctamente.');
        }
    }
}
