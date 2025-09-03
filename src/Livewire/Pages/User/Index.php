<?php

namespace Nawasara\Core\Livewire\Pages\User;

use Livewire\Component;
use Illuminate\Routing\Controllers\Middleware;

class Index extends Component
{
    public function render()
    {
        return view('nawasara-core::livewire.pages.user.index')->layout('nawasara-core::components.layouts.app');
    }
}
