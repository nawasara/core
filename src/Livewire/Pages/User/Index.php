<?php

namespace Nawasara\Core\Livewire\Pages\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Routing\Controllers\Middleware;

class Index extends Component
{
    use WithPagination;

    public $search;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // $this->authorize('user.view');
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.index', [
            'users' => User::search($this->search)->orderByDefault()->paginate(),
        ])->layout('nawasara-core::components.layouts.app');
    }

    public function delete($id)
    {
        $this->authorize('user.delete');

        $user = User::findOrFail($id);
        if (auth()->user()->id == $id) {
            // $this->alert('error', 'You can\'t delete yourself!');
            return;
        }


        $user->delete();

        // $this->alert('success', 'Success!');
        $this->dispatch('refreshComponent')->self();
    }
}
