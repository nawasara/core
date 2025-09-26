<?php

namespace Nawasara\Core\Livewire\SharedComponents;

use Livewire\Component;

class Breadcrumb extends Component
{
    public array $items = [];

    public function render()
    {
        return view('nawasara-core::livewire.shared-components.breadcrumb');
    }
}
