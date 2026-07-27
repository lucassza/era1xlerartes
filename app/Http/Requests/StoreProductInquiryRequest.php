<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'nullable',
                'uuid',
                Rule::exists('products', 'id'),
            ],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'min:6', 'max:30'],
            'company' => ['nullable', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'Você precisa confirmar que aceita receber contato comercial.',
            'website.prohibited' => 'Falha na validação anti-spam.',
            'email.email' => 'Informe um e-mail válido.',
            'message.required' => 'Por favor, descreva sua necessidade.',
            'message.min' => 'A mensagem deve ter ao menos :min caracteres.',
            'message.max' => 'A mensagem deve ter no máximo :max caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        if (is_string($email)) {
            $this->merge(['email' => mb_strtolower(trim($email))]);
        }

        $phone = $this->input('phone');
        if (is_string($phone)) {
            $this->merge(['phone' => preg_replace('/\s+/', '', $phone)]);
        }

        $state = $this->input('state');
        if (is_string($state)) {
            $this->merge(['state' => mb_strtoupper(trim($state))]);
        }
    }
}
