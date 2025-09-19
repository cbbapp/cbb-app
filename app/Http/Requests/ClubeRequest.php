<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClubeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // {clube} | {club} | {id}
        $routeModel = $this->route('clube') ?? $this->route('club') ?? $this->route('id') ?? null;
        $id = is_object($routeModel) ? ($routeModel->id ?? null) : ($routeModel ?? null);

        return [
            'nome'           => ['required','string','max:255'],
            'federacao_id'   => ['required','integer','exists:federacoes,id'],
            'cidade'         => ['required','string','max:120'],
            'estado'         => ['required','string','size:2'],
            'whatsapp_admin' => ['nullable','string','max:20'],

            // 1) Máscara amigável (##.###.###/####-##)
            'cnpj' => [
                'required',
                'regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
            ],

            // 2) Campo derivado com apenas dígitos para checar 14 dígitos + unique na coluna real
            'cnpj_digits' => [
                'required',
                'digits:14',
                Rule::unique('clubes', 'cnpj')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'           => 'O nome do clube é obrigatório.',
            'federacao_id.required'   => 'Selecione a federação.',
            'federacao_id.exists'     => 'Federação inválida.',
            'cidade.required'         => 'A cidade é obrigatória.',
            'estado.required'         => 'O estado (UF) é obrigatório.',
            'estado.size'             => 'Use a sigla do estado (ex.: RJ, SP).',

            'cnpj.required'           => 'O CNPJ é obrigatório.',
            'cnpj.regex'              => 'Informe o CNPJ no formato 00.000.000/0000-00.',

            'cnpj_digits.required'    => 'O CNPJ é obrigatório.',
            'cnpj_digits.digits'      => 'O CNPJ deve conter 14 dígitos (apenas números).',
            'cnpj_digits.unique'      => 'Já existe um clube com este CNPJ.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cnpj')) {
            $digits = preg_replace('/\D+/', '', (string) $this->input('cnpj'));
            $this->merge(['cnpj_digits' => $digits]);

            // Reaplica máscara, para o caso do usuário colar só dígitos
            if (strlen($digits) === 14) {
                $masked = substr($digits, 0, 2) . '.' .
                          substr($digits, 2, 3) . '.' .
                          substr($digits, 5, 3) . '/' .
                          substr($digits, 8, 4) . '-' .
                          substr($digits, 12, 2);
                $this->merge(['cnpj' => $masked]);
            }
        }
    }

    public function attributes(): array
    {
        return [
            'nome'           => 'Nome do clube',
            'federacao_id'   => 'Federação',
            'cnpj'           => 'CNPJ',
            'cnpj_digits'    => 'CNPJ',
            'cidade'         => 'Cidade',
            'estado'         => 'Estado (UF)',
            'whatsapp_admin' => 'WhatsApp do responsável',
        ];
    }
}
