<?php

namespace App\Traits;

use Exception;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

trait HasUser
{
    public $user;
    public ?Model $userModel;

    protected function getUser(): Authenticatable & Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new Exception(__('default.user_load_error'));
        }

        return $user;
    }

    protected function getModel(): mixed
    {
        $user = $this->getUser();

        $model = $user->roles->first()->name;

        return $user->$model;
    }
}