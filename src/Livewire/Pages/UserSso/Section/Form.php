<?php

namespace Nawasara\Core\Livewire\Pages\UserSso\Section;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;

class Form extends Component
{
    public function render()
    {
        return view('nawasara-core::livewire.pages.user-sso.section.form')
            ->layout('nawasara-core::components.layouts.app');
    }
}
