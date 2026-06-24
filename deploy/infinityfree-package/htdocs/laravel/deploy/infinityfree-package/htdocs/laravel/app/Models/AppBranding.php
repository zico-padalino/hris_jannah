<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBranding extends Model
{
    protected $table = 'app_branding';

    protected $fillable = [
        'app_name',
        'logo_path',
    ];

    public static function current(): self
    {
        $branding = static::query()->find(1);

        if ($branding !== null) {
            return $branding;
        }

        return static::query()->create([
            'app_name' => '',
            'logo_path' => null,
        ]);
    }
}
