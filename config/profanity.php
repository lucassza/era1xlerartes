<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Lista de termos bloqueados
    |--------------------------------------------------------------------------
    |
    | Termos comparados em fronteira de palavra (\b...) com regex
    | Unicode-aware. Tudo é convertido para minúsculas antes da checagem.
    | Mantenha a lista razoavelmente curta para reduzir falsos positivos.
    |
    */

    'terms' => [
        'buceta',
        'caralho',
        'cu',
        'cuzão',
        'foda',
        'fodase',
        'foder',
        'fuder',
        'gozar',
        'merda',
        'piru',
        'piroca',
        'porno',
        'porra',
        'puta',
        'puto',
        'safada',
        'safado',
        'tesão',
        'viado',
        'xereca',
        'xota',
    ],

    /*
    |--------------------------------------------------------------------------
    | Substituições opcionais
    |--------------------------------------------------------------------------
    |
    | Quando definido, o ProfanityFilter pode devolver um texto "limpo"
    | com os caracteres ofensivos substituídos pelo caractere informado.
    |
    */

    'replacement_character' => '*',

    /*
    |--------------------------------------------------------------------------
    | Habilitar filtro global
    |--------------------------------------------------------------------------
    |
    | Quando false, o ProfanityFilter sempre retornará "sem profanidade".
    | Útil para desabilitar em testes ou ambiente local.
    |
    */

    'enabled' => env('PROFANITY_FILTER_ENABLED', true),
];
