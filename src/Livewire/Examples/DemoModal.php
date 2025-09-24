<?php

namespace Nawasara\Core\Livewire\Examples;

use Livewire\Component;

class DemoModal extends Component
{
    public $message = 'livewire:nawasara-core.examples.demo-modal';

    public function render()
    {
        return view('nawasara-core::livewire.examples.demo-modal');
    }
}