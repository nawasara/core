<?php

namespace Nawasara\Core\Livewire\User\Section;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Nawasara\Core\Constants\Constants;
use Nawasara\Toaster\Concerns\HasToaster;

class Table extends Component
{
    use HasToaster;

    use WithPagination;

    public $params = [];

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    #[Computed]
    public function items()
    {
        return User::filter($this->params)->with(['roles'])->paginate();

    }
    
    #[On('confirm-delete')]
    public function delete($id)
    {
        Gate::authorize('nawasara-core.user.delete');

        $model = User::findOrFail($id);
        $model->delete();

        /* close modal */
        $this->dispatch('close-modal', id: 'modalConfirmDelete');

        /* show alert */
        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);
        
        /* refresh component */
        $this->dispatch('$refresh');
    }

    public function updatingfilter()
    {
        $this->resetPage();
    }

    #[On('filter')]
    public function filter($search = null, $selectedRole = null)
    {
        $this->params = [
            'search' => $search,
            'selectedRole' => $selectedRole,
        ];
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.section.table')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
