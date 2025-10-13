<?php

namespace Nawasara\Core\Livewire\Pages\Role\Section;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Nawasara\Core\Models\Role;
use Livewire\Attributes\Computed;

class Table extends Component
{
    use WithPagination;

    public $search = '';

    #[Computed]
    public function items()
    {
        return Role::search($this->search)->orderByDefault()->paginate();

    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.table')
            ->layout('nawasara-core::components.layouts.app');
    }

    public function delete($id)
    {
        $model = Role::findOrFail($id);
        $model->delete();

        toaster_success(Constants::NOTIFICATION_SUCCESS_CREATE);
        $this->redirectRoute('nawasara-core.roles.index', navigate: true);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
