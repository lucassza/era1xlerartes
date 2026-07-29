<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Selecione uma imagem para o avatar.',
            'avatar.image' => 'O avatar precisa ser uma imagem.',
            'avatar.mimes' => 'O avatar deve estar no formato JPG, PNG ou WebP.',
            'avatar.max' => 'O avatar não pode ultrapassar 2 MB.',
            'avatar.dimensions' => 'O avatar deve ter pelo menos 200 por 200 pixels.',
            'website.prohibited' => 'Falha na validação anti-spam.',
        ];
    }
}
