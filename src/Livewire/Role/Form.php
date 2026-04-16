<?php

namespace Nawasara\Core\Livewire\Role;

use Livewire\Component;
use Illuminate\Routing\Controllers\Middleware;

class Form extends Component
{
    public $id;
    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.form')->layout('nawasara-ui::components.layouts.app');
    }
}
