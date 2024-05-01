<?php

namespace litvinjuan\LaravelAfip\Transformers;

use Illuminate\Support\Arr;

class PersonaV1Transformer extends Transformer
{
    const ARRAY_KEYS = [
        'actividad',
        'domicilio',
        'impuesto',
        'categoria',
        'regimen',
        'relacion',
        'claveInactivaAsociada',
        'telefono',
        'email',
    ];

    public function transform(array $array): array
    {
        $array = $array['persona'];

        foreach (self::ARRAY_KEYS as $key) {
            $array[$key] = $this->normalizeArray(Arr::get($array, $key));
        }

        return $array;
    }

    private function normalizeArray(?array $array): array
    {
        if (! $array) {
            return [];
        }

        if (! array_is_list($array)) {
            return [$array];
        }

        return $array;
    }
}
