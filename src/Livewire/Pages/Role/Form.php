<?php

namespace Nawasara\Core\Livewire\Pages\Role;

use Livewire\Component;
use Illuminate\Routing\Controllers\Middleware;

class Form extends Component
{
    public $id;
    public function mound($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.form')->layout('nawasara-core::components.layouts.app');
    }
}
