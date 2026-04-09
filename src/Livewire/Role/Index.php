<?php

namespace Nawasara\Core\Livewire\Role;

use Livewire\Component;
use Illuminate\Routing\Controllers\Middleware;

class Index extends Component
{
    public function render()
    {
        return view('nawasara-core::livewire.pages.role.index')->layout('nawasara-ui::components.layouts.app');
    }
}
