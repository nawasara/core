<?php

namespace Nawasara\Core\Livewire\Modals;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\UserForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class FormUser extends Component
{
    public UserForm $form;

    public $roles;

    public $id;

    public function mount()
    {
        $this->roles = Role::all();
        if ($this->id) {
            $user = User::find($this->id);
            $this->form->setModel($user);
        }
    }

    public function initDataEdit()
    {
        if (!$this->id) return;

        $user = User::find($this->id);
        $this->form->setModel($user);
    }

    public function store($roles = [])
    {
        DB::beginTransaction();

        $user = $this->form->store();

        DB::commit();

        $this->form->reset();

        toaster_success(Constants::NOTIFICATION_SUCCESS_CREATE);

        $this->dispatch('$refresh'); // semua yg punya refresh component akan ke trigger

        $this->closeModal();
    }

    public function render()
    {
        return view('nawasara-core::livewire.modals.form-user');
    }
}
