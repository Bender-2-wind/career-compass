<?php

namespace App\Livewire\Pages\Auth;

use Filament\Forms\Form;
use Filament\Facades\Filament;
use App\Livewire\BaseFormComponent;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;

class PasswordFormComponent extends BaseFormComponent
{
    protected static string $view = 'livewire.profile.shared.password-form-component';

    public ?array $data = [];

    public function mount(): void
    {
        $this->user = $this->getUser();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Update Password'))
                    ->aside()
                    ->description(__('Ensure your account is using long, random password to stay secure.'))
                    ->schema([
                        TextInput::make('Current password')
                            ->label(__('Current Password'))
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->revealable(),
                        TextInput::make('password')
                            ->label(__('New Password'))
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation')
                            ->revealable(),
                        TextInput::make('passwordConfirmation')
                            ->label(__('Confirm Password'))
                            ->password()
                            ->required()
                            ->dehydrated(false)
                            ->revealable(),
                    ]),
            ])
            ->model($this->getUser())
            ->statePath('data');
    }

    public function updatePassword(): void
    {
        try {
            $data = $this->form->getState();

            $newData = [
                'password' => $data['password'],
            ];

            $this->user->update($newData);
        } catch (Halt $exception) {
            return;
        }

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->form->fill();

        Notification::make()
            ->success()
            ->title(__('Your profile information has been saved successfully.'))
            ->send();
    }
}
