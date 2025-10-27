<?php

namespace Nawasara\Core\Livewire\Pages\UserSso;

use Livewire\Component;
use Illuminate\Routing\Controllers\Middleware;

class Index extends Component
{
    public function render()
    {
        return view('nawasara-core::livewire.pages.user-sso.index')->layout('nawasara-core::components.layouts.app');
    }
}
