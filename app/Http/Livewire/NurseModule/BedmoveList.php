<?php

namespace App\Http\Livewire\NurseModule;

use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\Bed;
use App\Models\IpdBedmove;
use Livewire\Component;

class BedmoveList extends Component
{
    use WithCachedRows, WithPerPagePagination;

    public $bed_id = null;
    public $ipd_id = null;
    public $bed;
    public $selectedId;
    public $viewMode = 'ipd';
    public $isOpen = false;

    protected $listeners = [
        'delete:bedmove' => 'delete',
        'bml:open' => 'setOpen'
    ];

    protected $queryString = [
        'bed_id' => ['except' => '', 'as' => 'id']
    ];

    public function setOpen($val)
    {
        $this->bed_id = $val['bed_id'];
        $this->ipd_id = $val['ipd_id'];
        $this->viewMode = (($this->ipd_id != null)&&($this->ipd_id != '')) ? 'ipd' : 'bed';
        $this->isOpen = true;
    }

    public function deleteConfirm($id)
    {
        $this->selectedId = $id;

        $this->dispatchBrowserEvent('delete:confirm', [
            'action' => 'delete:bedmove'
        ]);
    }

    public function delete()
    {
        $bm = IpdBedmove::where('id', $this->selectedId)->first();
        $bm->delete();
        $this->dispatchBrowserEvent('toastify');
    }

    public function getRowsQueryProperty()
    {
        return
            IpdBedmove::when(($this->viewMode == 'ipd'), function($query) {
                return $query->where('ipd_id', $this->ipd_id);
            })
            ->when(($this->viewMode == 'bed'), function($query) {
                return $query->where('bed_id', $this->bed_id);
            });
    }

    public function update()
    {
        $lbm = $this->bed->lastBedmove();

        $empty = $lbm ?
                (($lbm->to_ref_id != 0 && $lbm->to_ref_id != null && $lbm->bedmove_type_id != config('ipd.moveout'))||($lbm->bedmove_type_id == config('ipd.moveout')))
                :
                true;
        $this->bed->empty_flag = $empty;
        $this->bed->save();
        $this->dispatchBrowserEvent('toastify');
    }

    public function getRowsProperty()
    {
        return $this->cache(function(){
            return $this->applyPagination($this->rowsQuery->orderBy('movedate', 'desc')
                ->orderBy('movetime', 'desc'));
        });
    }

    public function mount()
    {
        $this->bed = Bed::where('id', $this->bed_id)->first();
    }

    public function render()
    {
        return view('livewire.nurse-module.bedmove-list', [
            'rows' => $this->isOpen ? $this->rows : [],
        ]);
    }
}
