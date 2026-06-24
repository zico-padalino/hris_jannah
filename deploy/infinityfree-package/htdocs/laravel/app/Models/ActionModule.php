<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActionModule extends Model
{
    protected $fillable = [
        'module_key',
        'label',
        'sort_order',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(ModuleAction::class, 'module_key', 'module_key')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
