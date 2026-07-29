<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || $user->isBanned()) {
            return false;
        }

        $post = $this->route('post');

        return $post !== null && $post->commentsEnabled();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:5', 'max:800'],
            'score' => ['required', 'integer', 'between:1,5'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Escreva um comentário para enviar.',
            'content.min' => 'Seu comentário precisa ter ao menos :min caracteres.',
            'content.max' => 'Seu comentário pode ter no máximo :max caracteres.',
            'score.between' => 'A nota deve estar entre 1 e 5 estrelas.',
            'website.prohibited' => 'Falha na validação anti-spam.',
        ];
    }
}
