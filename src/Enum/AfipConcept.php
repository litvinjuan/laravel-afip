<?php

namespace litvinjuan\LaravelAfip\Enum;

enum AfipConcept: int
{
    case Productos = 1;
    case Servicios = 2;
    case ProductosYServicios = 3;

    public function description(): string
    {
        return match ($this) {
            self::Productos => 'Productos',
            self::Servicios => 'Servicios',
            self::ProductosYServicios => 'Productos y Servicios',
        };
    }

    public function includesServices(): bool
    {
        return match ($this) {
            self::Productos => false,
            self::Servicios, self::ProductosYServicios => false,
        };
    }
}
