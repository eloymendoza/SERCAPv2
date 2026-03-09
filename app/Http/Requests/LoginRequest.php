<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:255', // regex:/^[a-zA-Z0-9_]+$/
            'password' => 'required|string|min:3|max:255', // regex:/^[a-zA-Z0-9_]+$/
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'El campo :attribute es obligatorio.',
            'password.required' => 'El campo :attribute es obligatorio.',
            'username.string' => 'El campo :attribute debe ser una cadena de texto.',
            'password.string' => 'El campo :attribute debe ser una cadena de texto.',
            'username.min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'password.min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'username.max' => 'El campo :attribute debe tener menos de :max caracteres.',
            'password.max' => 'El campo :attribute debe tener menos de :max caracteres.',
            // 'username.regex' => 'El campo :attribute debe contener solo letras, números y guiones bajos.',
            // 'password.regex' => 'El campo :attribute debe contener solo letras, números y guiones bajos.',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'usuario',
            'password' => 'contraseña',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}