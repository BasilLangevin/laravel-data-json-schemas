<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Support;

use BasilLangevin\LaravelDataJsonSchemas\Schemas\ObjectSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Spatie\LaravelData\Data;

/**
 * The SchemaTree tracks the instances of each data class
 * within a schema. This allows the use of definitions
 * and references when assembling the schema array.
 */
class SchemaTree
{
    /**
     * The root data class for the tree.
     */
    protected string $rootClass = '';

    /**
     * The data classes that have already been transformed into schemas.
     *
     * @var array<class-string<Data>, ObjectSchema>
     */
    protected array $registeredSchemas = [];

    /**
     * The number of instances of each data class within the tree.
     *
     * @var array<class-string<Data>, int>
     */
    protected array $dataClassCounts = [];

    /**
     * Set the root data class for the tree.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function rootClass(string $dataClass): self
    {
        $this->rootClass = $dataClass;

        return $this;
    }

    /**
     * Register a schema for a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function registerSchema(string $dataClass, ObjectSchema $schema): void
    {
        $this->registeredSchemas[$dataClass] = $schema;
    }

    /**
     * Get the schema object for a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function getRegisteredSchema(string $dataClass): ObjectSchema
    {
        return $this->registeredSchemas[$dataClass];
    }

    /**
     * Check if a schema has already been registered for a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function hasRegisteredSchema(string $dataClass): bool
    {
        return isset($this->registeredSchemas[$dataClass]);
    }

    /**
     * Increment the count of instances of a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function incrementDataClassCount(string $dataClass): void
    {
        if (! isset($this->dataClassCounts[$dataClass])) {
            $this->dataClassCounts[$dataClass] = 0;
        }

        $this->dataClassCounts[$dataClass]++;
    }

    /**
     * Get the number of instances of a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function getDataClassCount(string $dataClass): int
    {
        return $this->dataClassCounts[$dataClass] ?? 0;
    }

    /**
     * Check if a data class has multiple instances within the tree.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function hasMultiple(string $dataClass): bool
    {
        return $this->getDataClassCount($dataClass) > 1;
    }

    /**
     * Get the reference names for all data classes within the tree.
     *
     * This allows the use of "$ref": "#/$defs/..." in the schema.
     *
     * @return array<string, string>
     */
    public function getRefNames(): array
    {
        $names = collect($this->dataClassCounts)->keys()
            ->mapWithKeys(fn (string $dataClass) => [$dataClass => str($dataClass)])
            ->map->whenExactly($this->rootClass, fn () => str('#'))
            ->map->afterLast('\\')
            ->map->whenEndsWith('Data', fn (Stringable $name) => $name->beforeLast('Data'))
            ->map->kebab()
            ->map->whenNotExactly('#', fn (Stringable $name) => $name->prepend('#/$defs/'))
            ->map->toString();

        /** @var Collection<string, string> $names */
        return $names->map(function (string $name, string $dataClass) use ($names) {
            /** @var int $index */
            $index = $names->keys()->search(fn (string $value) => $value === $dataClass);

            $duplicates = $names->values()->filter(fn (string $value) => $value === $name);

            if ($duplicates->count() === 1) {
                return $name;
            }

            $number = $duplicates->keys()->flip()->get($index) + 1;

            return $name.'-'.$number;
        })->all();
    }

    /**
     * Get the reference name for a data class.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function getRefName(string $dataClass): string
    {
        return $this->getRefNames()[$dataClass];
    }

    /**
     * Get the data classes that should be defined in the "$defs" section of the schema.
     *
     * @return Collection<int, class-string<Data>>
     */
    protected function getDefClasses(): Collection
    {
        return collect($this->dataClassCounts)
            ->filter(fn (int $count) => $count > 1)
            ->keys()
            ->filter(fn (string $dataClass) => $dataClass !== $this->rootClass);
    }

    /**
     * Check if the "$defs" section of the schema is needed.
     */
    public function hasDefs(): bool
    {
        return $this->getDefClasses()->isNotEmpty();
    }

    /**
     * Get the schema definitions for the "$defs" section of the schema.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getDefs(): array
    {
        return $this->getDefClasses()
            ->mapWithKeys(fn (string $dataClass) => $this->getDef($dataClass))
            ->all();
    }

    /**
     * Get the schema definition for a data class.
     *
     * @param  class-string<Data>  $dataClass
     * @return array<string, array<string, mixed>>
     */
    protected function getDef(string $dataClass): array
    {
        $name = Str::after($this->getRefName($dataClass), '#/$defs/');

        return [$name => $this->getRegisteredSchema($dataClass)->buildSchema()];
    }
}
