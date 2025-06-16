<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    /**
     * @return BelongsTo<Application,Resume>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
    protected $fillable = [
        'application_id',
        'resume',
        'resume_original_name',
    ];
}
