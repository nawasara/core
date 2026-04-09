<?php

namespace Nawasara\Core\Livewire\User\Modal;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Nawasara\Core\Constants\Constants;
use Nawasara\Toaster\Concerns\HasToaster;
use Nawasara\Core\Livewire\Forms\UserForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Nawasara\Core\Livewire\User\Section\Table;

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
        $this->roles = Role::all();

        $this->params = $params;
        self::initDataEdit();
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
