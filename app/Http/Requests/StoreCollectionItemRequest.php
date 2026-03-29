<?php

namespace App\Http\Requests;

use App\Services\SearchTribunalRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollectionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validTribunais = array_map('strtolower', app(SearchTribunalRegistry::class)->keys());

        return [
            'content_type' => ['required', 'string', 'in:tese,sumula'],
            'content_id' => ['required', 'integer', 'min:1'],
            'tribunal' => ['required', 'string', Rule::in($validTribunais)],
        ];
    }

    public function messages(): array
    {
        return [
            'content_type.required' => 'O tipo de conteúdo é obrigatório.',
            'content_type.in' => 'O tipo de conteúdo deve ser tese ou sumula.',
            'content_id.required' => 'O ID do conteúdo é obrigatório.',
            'content_id.integer' => 'O ID do conteúdo deve ser um número inteiro.',
            'tribunal.required' => 'O tribunal é obrigatório.',
            'tribunal.in' => 'O tribunal informado não é válido.',
        ];
    }
}
