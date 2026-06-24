<?php

namespace App\Http\Requests\Face;

use Illuminate\Foundation\Http\FormRequest;

class EnrollFaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:5120'],
            'face_descriptor' => ['required', 'array', 'min:64'],
            'face_descriptor.*' => ['numeric'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
