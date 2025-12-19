<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class Login extends Component
{
    public $darkMode = false;
    
    public function mount()
    {
        // Check if user has dark mode preference in browser
        $this->darkMode = request()->cookie('darkMode') === 'true';
    }
    
    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        cookie()->queue('darkMode', $this->darkMode ? 'true' : 'false', 525600); // 1 year
        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }
    
    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
