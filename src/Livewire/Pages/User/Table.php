<?php

namespace Nawasara\Core\Livewire\Pages\User;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;

class Table extends Component
{
    use WithPagination;

    public $params = [];

    #[Computed]
    public function items()
    {
        return User::filter($this->params)->with(['roles'])->paginate();

    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.table')
            ->layout('nawasara-core::components.layouts.app');
    }

    // #[On('export')]
    // public function export()
    // {
    //     return Excel::download(new PlantingActivityExport($this->params), 'data-kegiatan-tanam-pohon-'.date('Ymd').'.xlsx');
    // }

    // public function exportDetailPdf($id)
    // {
    //     return PlantingActivityDetailExport::download($id);
    // }

    public function delete($id)
    {
        // $this->authorize('kegiatan.penanaman.pohon.delete');
        $model = User::findOrFail($id);
        $model->delete();

        // $this->alert('success', 'Success!');
        $this->redirectRoute('nawasara-core.users.index', navigate: true);
    }

    public function updatingfilter()
    {
        $this->resetPage();
    }

    #[On('filter')]
    public function filter($search = null, $selectedRole = null)
    {
        $params = [
            'search' => $search,
            'selectedRole' => $selectedRole
        ];

        $this->params = $params;

        info($params);

    }
}
