<?php
namespace Nawasara\Core\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Login extends Component
{
    public $email = '';
    public $password = '';

    public function login()
    {
        $credentials = Validator::make([
            'email' => $this->email,
            'password' => $this->password,
        ], [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ])->validate();

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            return redirect()->intended('/home');
        }

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.auth.login')
            ->layout('nawasara-ui::components.layouts.guest');
    }
}
