<?php

namespace App\Filament\Pages\Auth;

use Filament\Panel;
use App\Traits\HasUser;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Route;

class EditProfile extends Page
{
    use HasUser;

    protected static string $view = 'filament.pages.auth.edit-profile';

    public ?array $data = [];

    protected static bool $isDiscovered = false;

    public static function getSlug(): string
    {
        return static::$slug ?? 'profile';
    }

    public static function registerRoutes(Panel $panel): void
    {
        if (filled(static::getCluster())) {
            Route::name(static::prependClusterRouteBaseName(''))
                ->prefix(static::prependClusterSlug(''))
                ->group(fn() => static::routes($panel));

            return;
        }

        static::routes($panel);
    }

    public static function getLabel(): string
    {
        return __('filament-panels::pages/auth/edit-profile.label');
    }

}