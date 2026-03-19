<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas;

use BadMethodCallException;
use BasilLangevin\LaravelDataJsonSchemas\Actions\MakeSchemaForReflectionType;
use BasilLangevin\LaravelDataJsonSchemas\Actions\TransformDataClassToSchema;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns\ConstructsSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns\HasKeywords;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Contracts\Schema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Contracts\SingleTypeSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\AnnotationKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\ArraySchemaKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\CompositionKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\GeneralKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\NumberSchemaKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\ObjectSchemaKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\StringSchemaKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\PipeCallbacks;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\WhenCallbacks;
use BasilLangevin\LaravelDataJsonSchemas\Support\PropertyWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\SchemaTree;
use Illuminate\Support\Collection;
use ReflectionNamedType;
use Spatie\LaravelData\Data;

class UnionSchema implements Schema
{
    // General DocBlock annotations
    use AnnotationKeywordMethodAnnotations;

    // Constituent schemas DocBlock annotations
    use ArraySchemaKeywordMethodAnnotations;
    use CompositionKeywordMethodAnnotations;
    use ConstructsSchema;
    use GeneralKeywordMethodAnnotations;

    // Traits
    use HasKeywords {
        __call as __callUnionKeyword;
    }
    use NumberSchemaKeywordMethodAnnotations;
    use ObjectSchemaKeywordMethodAnnotations;
    use PipeCallbacks;
    use StringSchemaKeywordMethodAnnotations;
    use WhenCallbacks;

    /**
     * @var array<class-string<Keyword>|array<class-string<Keyword>>>
     */
    public static array $keywords = [
        Keyword::ANNOTATION_KEYWORDS,
    ];

    /**
     * The constituent schemas of the union.
     *
     * @var Collection<int, SingleTypeSchema>
     */
    protected Collection $constituentSchemas;

    /**
     * Get the constituent schemas of the union.
     *
     * @return Collection<int, SingleTypeSchema>
     */
    public function getConstituentSchemas(): Collection
    {
        return $this->constituentSchemas;
    }

    public function buildConstituentSchemas(PropertyWrapper $property, SchemaTree $tree): static
    {
        $this->constituentSchemas = $property->getReflectionTypes()
            ->map(fn (ReflectionNamedType $type) => $this->makeConstituentSchema($type, $tree));

        $includesNull = $this->constituentSchemas->contains(fn (SingleTypeSchema $schema) => $schema instanceof NullSchema);

        if ($property->isNullable() && ! $includesNull) {
            $this->constituentSchemas->push(NullSchema::make());
        }

        if (! $this->canBeConsolidated()) {
            $this->constituentSchemas->each->applyType();
        }

        return $this;
    }

    protected function makeConstituentSchema(ReflectionNamedType $type, SchemaTree $tree): SingleTypeSchema
    {
        if (is_subclass_of($type->getName(), Data::class)) {
            return TransformDataClassToSchema::run($type->getName(), $tree);
        }

        $action = new MakeSchemaForReflectionType(unionNullableTypes: false);

        /** @var SingleTypeSchema $schema */
        $schema = $action->handle($type);

        return $schema;
    }

    public function tree(SchemaTree $tree): static
    {
        $this->getConstituentSchemas()->each->tree($tree);

        return $this;
    }

    /**
     * Allow keyword methods to be called on the schema type.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     */
    public function __call(mixed $name, mixed $arguments): mixed
    {
        try {
            return $this->__callUnionKeyword($name, $arguments);
        } catch (BadMethodCallException $e) {
        }

        $badCalls = 0;

        $results = $this->getConstituentSchemas()
            ->map(function (SingleTypeSchema $schema) use ($name, $arguments, &$badCalls) {
                try {
                    return $schema->__call($name, $arguments);
                } catch (BadMethodCallException $e) {
                    $badCalls++;

                    return $e;
                }
            })
            ->reject(fn ($result) => $result instanceof BadMethodCallException);

        if ($badCalls === $this->getConstituentSchemas()->count()) {
            throw new BadMethodCallException("Method \"{$name}\" not found");
        }

        if ($results->every(fn ($result) => $result instanceof Schema)) {
            return $this;
        }

        if ($results->count() === 1) {
            return $results->first();
        }

        return $results;
    }

    public function cloneBaseStructure(): static
    {
        $clone = new static;

        $clone->constituentSchemas = $this->constituentSchemas
            ->map(fn (SingleTypeSchema $schema) => $schema->cloneBaseStructure());

        return $clone;
    }

    /**
     * Check if the constituent schemas can be consolidated into a single schema.
     */
    protected function canBeConsolidated(): bool
    {
        return $this->getConstituentSchemas()
            ->doesntContain(fn (SingleTypeSchema $schema) => $schema instanceof ObjectSchema);
    }

    /**
     * Consolidate the constituent schemas into a single schema.
     *
     * @return array<string, mixed>
     */
    protected function buildConsolidatedSchema(): array
    {
        $types = $this->getConstituentSchemas()
            ->map(fn (SingleTypeSchema $schema) => $schema::getDataType())
            ->map->value
            ->toArray();

        /** @var array<int, array<string, mixed>> */
        $constituentSchemas = $this->getConstituentSchemas()
            ->map->toArray(true)
            ->toArray();

        $mergedSchemas = array_merge(...$constituentSchemas);

        /** @var array<int, array<string, mixed>> */
        $notKeywords = collect($constituentSchemas)->pluck('not')->filter()->toArray();
        if ($notKeywords) {
            $mergedSchemas['not'] = array_merge(...$notKeywords);
        }

        return [
            ...$this->buildSchema(),
            'type' => $types,
            ...$mergedSchemas,
        ];
    }

    /**
     * Consolidate the constituent schemas into an anyOf schema.
     *
     * @return array<string, mixed>
     */
    protected function buildAnyOfSchema(): array
    {
        $constituentSchemas = $this->getConstituentSchemas()
            ->map->toArray(true)
            ->toArray();

        return [
            ...$this->buildSchema(),
            'anyOf' => $constituentSchemas,
        ];
    }

    /**
     * Convert the schema to an array.
     */
    public function toArray(bool $nested = false): array
    {
        return $this->canBeConsolidated()
            ? $this->buildConsolidatedSchema()
            : $this->buildAnyOfSchema();
    }
}
