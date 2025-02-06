<?php

namespace BasilLangevin\LaravelDataSchemas\Actions;

use BasilLangevin\LaravelDataSchemas\Actions\Concerns\Runnable;
use BasilLangevin\LaravelDataSchemas\Schemas\Contracts\Schema;
use BasilLangevin\LaravelDataSchemas\Schemas\UnionSchema;
use BasilLangevin\LaravelDataSchemas\Support\PropertyWrapper;
use BasilLangevin\LaravelDataSchemas\Support\SchemaTree;

class SetupSchema
{
    /** @use Runnable<array{Schema, PropertyWrapper, SchemaTree}, Schema> */
    use Runnable;

    /**
     * Instantiate any constituent Schemas and add the "type" keyword.
     */
    public function handle(Schema $schema, PropertyWrapper $property, SchemaTree $tree): Schema
    {
        if ($schema instanceof UnionSchema) {
            return $schema->buildConstituentSchemas($property, $tree);
        }

        return $schema->applyType();
    }
}
