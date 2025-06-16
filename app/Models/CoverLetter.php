<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverLetter extends Model
{
    /**
     * @return BelongsTo<Application,CoverLetter>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
        protected $fillable = [
        'application_id',
        'cover_letter',
        'cover_letter_original_name',
    ];
}
