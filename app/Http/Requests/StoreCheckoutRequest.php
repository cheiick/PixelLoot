<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour les données de checkout.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => [
                'bail',
                'required',
                'string',
                'regex:/^\+?[0-9\s().-]{7,24}$/',
                Rule::unique('users', 'phone')->ignore($this->user()->id),
            ],
            'coupon_code' => ['bail', 'nullable', 'string', 'max:50', 'exists:coupons,code'],
            'payment_method' => ['sometimes', 'string', 'in:stripe,fedapay'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        $this->merge([
            'phone' => is_string($phone) ? preg_replace('/[^\d+]/', '', trim($phone)) : $phone,
        ]);
    }

    /**
     * Messages de validation personnalisés.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'Veuillez fournir une adresse e-mail valide.',
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom de famille est requis.',
            'address.required' => 'L\'adresse est requise.',
            'city.required' => 'La ville est requise.',
            'postal_code.required' => 'Le code postal est requis.',
            'country.required' => 'Le pays est requis.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.regex' => 'Veuillez saisir un numéro de téléphone valide.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre compte.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
            'coupon_code.exists' => 'Le code coupon fourni n\'est pas valide.',
        ];
    }
}