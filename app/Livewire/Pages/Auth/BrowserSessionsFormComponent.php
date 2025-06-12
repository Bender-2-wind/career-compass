<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\BaseFormComponent;
use Carbon\Carbon;
use Filament\Forms\Form;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;

class BrowserSessionsFormComponent extends BaseFormComponent
{
    protected string $view = 'livewire.pages.auth.browser-sessions-form-component';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Browser Sessions'))
                    ->description(__('Manage and log out your active sessions on other browsers and devices.'))
                    ->aside()
                    ->schema([
                        ViewField::make('browserSessions')
                            ->label(__(__('Browser Sessions')))
                            ->hiddenLabel()
                            ->view('livewire.components.browser-sessions')
                            ->viewData(['data' => self::getSessions()]),
                        Actions::make([
                            Actions\Action::make('deleteBrowserSessions')
                                ->label(__('Log Out Other Browser Sessions'))
                                ->requiresConfirmation()
                                ->modalHeading(__('Log Out Other Browser Sessions'))
                                ->modalDescription(__('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.'))
                                ->modalSubmitActionLabel(__('Log Out Other Browser Sessions'))
                                ->form([
                                    TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('Password'))
                                        ->required(),
                                ])
                                ->action(function (array $data) {
                                    self::logoutOtherBrowserSessions($data['password']);
                                })
                                ->modalWidth('2xl'),
                        ]),

                    ]),
            ]);
    }

    public static function getSessions(): array
    {
        if (config(key: 'session.driver') !== 'database') {
            return [];
        }

        return collect(
            value: DB::connection(config(key: 'session.connection'))->table(table: config(key: 'session.table', default: 'sessions'))
                ->where(column: 'user_id', operator: Auth::user()->getAuthIdentifier())
                ->latest(column: 'last_activity')
                ->get()
        )->map(callback: function ($session): object {
            $agent = self::createAgent($session);

            return (object) [
                'device' => [
                    'browser' => $agent->browser(),
                    'desktop' => $agent->isDesktop(),
                    'mobile' => $agent->isMobile(),
                    'tablet' => $agent->isTablet(),
                    'platform' => $agent->platform(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        })->toArray();
    }

    protected static function createAgent(mixed $session)
    {
        return tap(
            value: new Agent,
            callback: fn ($agent) => $agent->setUserAgent(userAgent: $session->user_agent)
        );
    }

    public static function logoutOtherBrowserSessions($password): void
    {
        if (! Hash::check($password, Auth::user()->password)) {
            Notification::make()
                ->danger()
                ->title(__('The password you entered was incorrect. Please try again.'))
                ->send();

            return;
        }

        Auth::guard()->logoutOtherDevices($password);

        request()->session()->put([
            'password_hash_' . Auth::getDefaultDriver() => Auth::user()->getAuthPassword(),
        ]);

        self::deleteOtherSessionRecords();
    }

    protected static function deleteOtherSessionRecords()
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', Auth::user()->getAuthIdentifier())
            ->where('id', '!=', request()->session()->getId())
            ->delete();
    }
}
