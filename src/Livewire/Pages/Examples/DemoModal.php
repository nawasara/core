<?php

namespace Nawasara\Core\Livewire\Pages\Examples;

use Livewire\Component;

class DemoModal extends Component
{
    public $message = 'livewire:nawasara-core.pages.examples.demo-modal';

    public function render()
    {
        return view('nawasara-core::livewire.pages.examples.demo-modal');
    }
}