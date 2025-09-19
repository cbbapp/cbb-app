<?php

return [

    'required' => 'O campo :attribute é obrigatório.',
    'email'    => 'O campo :attribute deve ser um e-mail válido.',
    'unique'   => 'O valor do campo :attribute já está em uso.',
    'regex'    => 'O formato do campo :attribute é inválido.',
    'max'      => [
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'size' => [
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],

    'attributes' => [
        'nome'       => 'Nome da Entidade',
        'sigla'      => 'Sigla',
        'presidente' => 'Presidente',
        'cnpj'       => 'CNPJ',
        'email'      => 'E-mail',
        'telefone'   => 'Telefone',
        'endereco'   => 'Endereço',
        'cidade'     => 'Cidade',
        'estado'     => 'Estado (UF)',
        'site'       => 'Site',
    ],

];
