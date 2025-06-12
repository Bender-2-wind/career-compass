<?php

namespace App\Livewire;

use App\Traits\HasUser;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class BaseFormComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use HasUser;
}