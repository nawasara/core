<?php

namespace Nawasara\Core\Livewire\Components;

use Livewire\Component;

class UniversalModal extends Component
{
    public $open = false;
    public $title = '';
    public $component = null;
    public $params = [];

    // protected $listeners = ['openModal' => 'open', 'closeModal' => 'close'];

    public function load($payload)
    {
        $this->title = $payload['title'] ?? '';
        $this->component = $payload['component'] ?? null;
        $this->params = $payload['params'] ?? [];
    }

    public function render()
    {
        return view('nawasara-core::livewire.components.universal-modal');
    }
}
