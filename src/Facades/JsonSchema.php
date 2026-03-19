<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Facades;

use BasilLangevin\LaravelDataJsonSchemas\LaravelDataJsonSchemas;
use Illuminate\Support\Facades\Facade;

/**
 * @see LaravelDataJsonSchemas
 */
class JsonSchema extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BasilLangevin\LaravelDataJsonSchemas\JsonSchema::class;
    }
}
