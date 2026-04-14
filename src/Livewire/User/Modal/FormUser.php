<?php

namespace Nawasara\Core\Livewire\User\Modal;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\UserForm;
use Nawasara\Core\Livewire\User\Section\Table;
use Nawasara\Toaster\Concerns\HasToaster;
use Spatie\Permission\Models\Role;

class FormUser extends Component
{
    use HasToaster;

    public UserForm $form;

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    public $roles;

    public $params = [];

    public function mount($params = [])
    {
        Gate::authorize(isset($params['id']) ? 'nawasara-core.user.edit' : 'nawasara-core.user.create');

        $this->roles = Role::all();

        $this->params = $params;
        $this->initDataEdit();
    }
    
    public function initDataEdit()
    {
        if (!isset($this->params['id'])) return;

        $user = User::find($this->params['id']);
        $this->form->setModel($user);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.modal.form-user');
    }

    #[On('store')]
    public function store($roles = [])
    {
        Gate::authorize(isset($this->params['id']) ? 'nawasara-core.user.edit' : 'nawasara-core.user.create');

        DB::beginTransaction();

        $this->form->setRoles($roles);

        $this->form->store();

        DB::commit();

        /* close modal */
        $this->dispatch('close-livewire-modal', id: 'modal-user-form');
        
        /* show toaster */
        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);

        $this->redirect(route('nawasara-core.user.index'), navigate: true);

    }
}
