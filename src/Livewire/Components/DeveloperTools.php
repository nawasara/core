<?php

namespace Nawasara\Core\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class DeveloperTools extends Component
{
    public $isOpen = false;
    public $output = '';
    public $commandRunning = false;
    public $currentCommand = '';

    public function toggleTools()
    {
        $this->isOpen = !$this->isOpen;
        if (!$this->isOpen) {
            $this->resetOutput();
        }
    }

    public function runCommand($command)
    {
        $this->commandRunning = true;
        $this->currentCommand = $command;
        $this->output = "Running: {$command}\n\n";

        try {
            // Create a buffered output to capture the command output
            $output = new BufferedOutput();
            
            // Run the artisan command
            Artisan::call($command, [], $output);
            
            // Get the command output
            $this->output .= $output->fetch();
            $this->output .= "\n\nCommand executed successfully!";
        } catch (\Exception $e) {
            $this->output .= "\n\nError: " . $e->getMessage();
        }

        $this->commandRunning = false;
    }

    public function runMigrateFresh()
    {
        $this->runCommand('migrate:fresh');
    }

    public function runMigrateFreshSeed()
    {
        $this->runCommand('migrate:fresh --seed');
    }

    public function runDbSeed()
    {
        $this->runCommand('db:seed');
    }

    public function runVendorPublish()
    {
        $this->runCommand('vendor:publish --all');
    }

    public function runClearCache()
    {
        $this->runCommand('cache:clear');
    }

    public function runRouteClear()
    {
        $this->runCommand('route:clear');
    }

    public function runOptimize()
    {
        $this->runCommand('optimize');
    }

    public function runStorageLink()
    {
        $this->runCommand('storage:link');
    }

    public function resetOutput()
    {
        $this->output = null;
        $this->currentCommand = null;
    }

    public function render()
    {
        return view('nawasara-core::livewire.components.developer-tools');
    }
}