<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'face_descriptor' => ['required', 'array', 'min:64'],
            'face_descriptor.*' => ['numeric'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
