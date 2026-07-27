<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isBanned();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:20', 'max:1000'],
            'score' => ['required', 'integer', 'between:1,5'],
            'city' => ['nullable', 'string', 'max:120'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Conte seu depoimento para continuar.',
            'content.min' => 'Seu depoimento precisa ter ao menos :min caracteres.',
            'content.max' => 'Seu depoimento pode ter no máximo :max caracteres.',
            'score.between' => 'A nota deve estar entre 1 e 5 estrelas.',
            'website.prohibited' => 'Falha na validação anti-spam.',
        ];
    }
}
