<?php

namespace litvinjuan\LaravelAfip\Transformers;

abstract class Transformer
{
    abstract public function transform(array $array): array;
}
