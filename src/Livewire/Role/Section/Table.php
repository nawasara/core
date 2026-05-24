<?php

namespace Nawasara\Core\Livewire\Role\Section;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Core\Attributes\RequiresSudo;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Models\Role;
use Nawasara\Core\Traits\WithSudo;
use Nawasara\Toaster\Concerns\HasToaster;

class Table extends Component
{
    use HasToaster;
    use WithPagination;
    use WithSudo;

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
    #[RequiresSudo(reason: 'menghapus role')]
    public function delete($id)
    {
        Gate::authorize('core.role.delete');

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
