<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeFace extends Model
{
    protected $fillable = [
        'employee_id',
        'photo_path',
        'face_descriptor',
        'is_primary',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'face_descriptor' => 'array',
            'is_primary' => 'boolean',
            'enrolled_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path) && Storage::disk('public')->exists($this->photo_path);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->hasPhoto()) {
                return null;
            }

            return '/storage/'.ltrim($this->photo_path, '/');
        });
    }
}
