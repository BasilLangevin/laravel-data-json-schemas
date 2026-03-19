<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns;

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\PipeCallbacks;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\WhenCallbacks;
use BasilLangevin\LaravelDataJsonSchemas\Support\SchemaTree;

/**
 * @property DataType $type The type of the schema.
 *
 * @method static type(DataType $type) Set the type of the schema using the TypeKeyword class.
 */
trait SingleTypeSchemaTrait
{
    use ConstructsSchema;
    use HasKeywords;
    use PipeCallbacks;
    use WhenCallbacks;

    protected SchemaTree $tree;

    public static function getDataType(): DataType
    {
        if (! property_exists(static::class, 'type')) {
            throw new \Exception('SingleType schemas must have a $type.');
        }

        return static::class::$type;
    }

    /**
     * Apply the type keyword to the schema.
     */
    public function applyType(): static
    {
        return $this->type(static::getDataType());
    }

    /**
     * Clone the base structure of the schema.
     */
    public function cloneBaseStructure(): static
    {
        return new static;
    }

    /**
     * Set the tree for the schema.
     */
    public function tree(SchemaTree $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $nested = false): array
    {
        return $this->buildSchema();
    }
}
