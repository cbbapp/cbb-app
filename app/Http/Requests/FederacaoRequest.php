<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FederacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Detecta id em {federacao} | {federation} | {id}
        $routeModel = $this->route('federacao') ?? $this->route('federation') ?? $this->route('id') ?? null;
        $id = is_object($routeModel) ? ($routeModel->id ?? null) : ($routeModel ?? null);

        return [
            'nome'       => ['required', 'string', 'max:255'],
            'sigla'      => ['required', 'string', 'max:10'],
            'presidente' => ['required', 'string', 'max:255'],

            'email'    => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade'   => ['nullable', 'string', 'max:120'],
            'estado'   => ['nullable', 'string', 'max:2'],
            'site'     => ['nullable', 'string', 'max:255'],

            // 1) Máscara para UX
            'cnpj' => [
                'required',
                'regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
            ],

            // 2) Dígitos (derivado) para unicidade no banco (coluna 'cnpj' guarda só dígitos)
            'cnpj_digits' => [
                'required',
                'digits:14',
                Rule::unique('federacoes', 'cnpj')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'       => 'O campo Nome da Entidade é obrigatório.',
            'sigla.required'      => 'O campo Sigla é obrigatório.',
            'presidente.required' => 'O campo Presidente é obrigatório.',

            'cnpj.required'       => 'O CNPJ é obrigatório.',
            'cnpj.regex'          => 'Informe o CNPJ no formato 00.000.000/0000-00.',

            'cnpj_digits.required'=> 'O CNPJ é obrigatório.',
            'cnpj_digits.digits'  => 'O CNPJ deve conter 14 dígitos válidos.',
            'cnpj_digits.unique'  => 'Já existe uma federação cadastrada com este CNPJ.',

            'required' => 'O campo :attribute é obrigatório.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cnpj')) {
            $digits = preg_replace('/\D+/', '', (string)$this->input('cnpj'));
            $this->merge(['cnpj_digits' => $digits]);

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
            'nome'        => 'Nome da Entidade',
            'sigla'       => 'Sigla',
            'presidente'  => 'Presidente',
            'cnpj'        => 'CNPJ',
            'cnpj_digits' => 'CNPJ',
            'email'       => 'E-mail',
            'telefone'    => 'Telefone',
            'endereco'    => 'Endereço',
            'cidade'      => 'Cidade',
            'estado'      => 'Estado (UF)',
            'site'        => 'Site',
        ];
    }
}
