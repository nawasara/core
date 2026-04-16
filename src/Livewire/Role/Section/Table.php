<?php

namespace Nawasara\Core\Livewire\Role\Section;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Models\Role;
use Nawasara\Toaster\Concerns\HasToaster;

class Table extends Component
{
    use HasToaster;
    use WithPagination;

    public $search = '';

    #[Computed]
    public function items()
    {
        return Role::search($this->search)->orderByDefault()->paginate(100);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.table')
            ->layout('nawasara-ui::components.layouts.app');
    }

    #[On('confirm-delete')]
    public function delete($id)
    {
        Gate::authorize('nawasara-core.role.delete');

        $model = Role::findOrFail($id);
        $model->delete();

        /* close modal */
        $this->dispatch('close-modal', id: 'modalConfirmDelete');

        /* show alert */
        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);
        
        /* refresh component */
        $this->dispatch('$refresh');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
