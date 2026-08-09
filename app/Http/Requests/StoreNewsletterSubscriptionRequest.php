<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:254'],
            'company' => ['nullable', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Enter your email address to join the list.',
            'email.email' => 'Enter a valid email address.',
        ];
    }
}
