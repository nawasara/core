<?php

namespace Nawasara\Core\Livewire\Examples;

use Livewire\Component;

class DemoModal extends Component
{
    public $message = 'Ini konten dari Livewire di modal!';

    public function render()
    {
        return view('nawasara-core::livewire.examples.demo-modal');
    }
}