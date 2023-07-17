<?php

namespace litvinjuan\LaravelAfip\Transformers;

use Illuminate\Support\Arr;

class PersonaV2Transformer extends Transformer
{
    const SECTIONS = [
        'datosGenerales' => [
            'caracterizacion',
        ],
        'datosRegimenGeneral' => [
            'impuesto',
            'categoriaAutonomo',
            'regimen',
            'actividad',
        ],
        'datosMonotributo' => [
            'componenteDeSociedad',
            'actividad',
        ],
        'errorConstancia' => [
            'error',
        ],
        'errorRegimenGeneral' => [
            'error',
        ],
        'errorMonotributo' => [
            'error',
        ],
    ];

    public function transform(array $array): array
    {

        return collect(self::SECTIONS)->map(function ($subsections, $section) use ($array) {
            if (! Arr::has($array, $section)) {
                return null;
            }

            $arrays = collect($subsections)->mapWithKeys(function ($subsection) use ($array, $section) {
                if (! Arr::has($array[$section], $subsection)) {
                    return [$subsection => []];
                }

                if (! is_array($array[$section][$subsection])) {
                    return [$subsection => [$array[$section][$subsection]]];
                }

                if (array_is_list($array[$section][$subsection])) {
                    return [$subsection => [$array[$section][$subsection]]];
                }

                return [$subsection => $array[$section][$subsection]];
            });

            return collect($array[$section])->merge($arrays)->toArray();
        })->toArray();
    }
}
