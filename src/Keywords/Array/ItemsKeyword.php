<?php

namespace BasilLangevin\LaravelDataSchemas\Keywords\Array;

use BasilLangevin\LaravelDataSchemas\Keywords\Contracts\HandlesMultipleInstances;
use BasilLangevin\LaravelDataSchemas\Keywords\Keyword;
use BasilLangevin\LaravelDataSchemas\Schemas\Contracts\Schema;
use Illuminate\Support\Collection;

class ItemsKeyword extends Keyword implements HandlesMultipleInstances
{
    public function __construct(protected Schema $value) {}

    /**
     * Get the value of the keyword.
     */
    public function get(): Schema
    {
        return $this->value;
    }

    /**
     * Add the definition for the keyword to the given schema.
     */
    public function apply(Collection $schema): Collection
    {
        return $schema->merge(['items' => $this->get()->toArray()]);
    }

    /**
     * Apply the keyword to the schema for multiple values.
     */
    public static function applyMultiple(Collection $schema, Collection $instances): Collection
    {
        return $schema->merge(['items' => [
            'anyOf' => $instances->map->get()->toArray(),
        ]]);
    }
}
