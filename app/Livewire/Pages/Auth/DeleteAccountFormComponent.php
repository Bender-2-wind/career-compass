<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\BaseFormComponent;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;

class DeleteAccountFormComponent extends BaseFormComponent
{
    protected string $view = 'livewire.pages.auth.delete-account-form-component';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Delete Account'))
                    ->description(__('Permanently delete your account.'))
                    ->aside()
                    ->schema([
                        ViewField::make('deleteAccount')
                            ->label(__('Delete Account'))
                            ->hiddenLabel()
                            ->view('livewire.components.delete-account'),
                        Actions::make([
                            Actions\Action::make('deleteAccount')
                                ->label(__('Delete Account'))
                                ->icon('heroicon-m-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading(__('Delete Account'))
                                ->modalDescription(__('Are you sure you would like to delete your account? This cannot be undone!'))
                                ->modalSubmitActionLabel(__('Yes, delete it!'))
                                ->form([
                                    TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('Password'))
                                        ->required(),
                                ])
                                ->action(function (array $data) {

                                    if (! Hash::check($data['password'], Auth::user()->password)) {
                                        $this->sendErrorDeleteAccount(__('The password you entered was incorrect. Please try again.'));

                                        return;
                                    }

                                    auth()->user()?->delete();
                                }),
                        ]),
                    ]),
            ]);
    }

    public function sendErrorDeleteAccount(string $message): void
    {
        Notification::make()
            ->danger()
            ->title($message)
            ->send();
    }
}
