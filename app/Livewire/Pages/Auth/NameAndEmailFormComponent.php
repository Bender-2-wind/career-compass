<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\BaseFormComponent;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class NameAndEmailFormComponent extends BaseFormComponent
{
    protected static string $view = 'livewire.profile.shared.name-and-email-form-component';

    public ?array $data = [];

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->form->fill($this->user->only(['name', 'email']));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Name And Email'))
                    ->aside()
                    ->description(__('Update your account user name and email'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name')),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->unique('users', ignorable: $this->user),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateNameAndEmail(): void
    {
        try {
            $data = $this->form->getState();

            $this->user->update($data);
        } catch (Halt $exception) {
            return;
        }

        Notification::make()
            ->success()
            ->title(__('Your user name and/or email has been changed successfully.'))
            ->send();
    }
}
