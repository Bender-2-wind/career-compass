<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Auth\EditProfile as FilamentEditProfile;

class EditProfile extends FilamentEditProfile
{
    protected static string $view = 'livewire.pages.auth.edit-profile';

    protected function getForms(): array
    {
        $forms = [
            'pages.auth.name-and-email-form-component',
            'pages.auth.password-form-component',
            'pages.auth.browser-sessions-form-component',
            'pages.auth.delete-account-form-component',
        ];


        return $forms;
    }
}
