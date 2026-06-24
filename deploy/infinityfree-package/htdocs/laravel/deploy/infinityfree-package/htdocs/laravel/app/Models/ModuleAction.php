<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleAction extends Model
{
    protected $fillable = [
        'module_key',
        'action_key',
        'label',
        'icon_type',
        'sort_order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ActionModule::class, 'module_key', 'module_key');
    }
}
