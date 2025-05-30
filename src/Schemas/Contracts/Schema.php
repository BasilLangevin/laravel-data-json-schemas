<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas\Contracts;

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Enums\Format;
use BasilLangevin\LaravelDataJsonSchemas\Enums\JsonSchemaDialect;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use BasilLangevin\LaravelDataJsonSchemas\Support\Contracts\Pipeable;
use BasilLangevin\LaravelDataJsonSchemas\Support\Contracts\Whenable;
use BasilLangevin\LaravelDataJsonSchemas\Support\SchemaTree;
use Closure;
use Illuminate\Support\Collection;

/**
 * A schema is a representation of a JSON Schema for a specific type/union of types.
 *
 * Schemas can be nested within one another to create more complex schemas.
 *
 * @property array<class-string<Keyword>|array<class-string<Keyword>>> $keywords The keywords that are available for this schema type.
 *
 * @see https://json-schema.org/draft/2020-12/json-schema-validation
 *
 * --------------------------------------------------------------------------
 *
 * @method static dialect(JsonSchemaDialect $dialect) Set the dialect keyword of the schema.
 * @method JsonSchemaDialect|Collection<int, JsonSchemaDialect> getDialect() Get the value(s) passed to the dialect method.
 * @method static title(string $title) Set the title keyword of the schema.
 * @method string|Collection<int, string> getTitle() Get the value(s) passed to the title method.
 * @method static description(string $description) Set the description keyword of the schema.
 * @method string|Collection<int, string> getDescription() Get the value(s) passed to the description method.
 * @method static default(mixed $default) Set the default keyword of the schema.
 * @method mixed|Collection<int, mixed> getDefault() Get the value(s) passed to the default method.
 * @method static customAnnotation(string|array<string, string> $annotation, ?string $value = null) Set a custom annotation on the schema.
 * @method array<string, string>|Collection<int, array<string, string>> getCustomAnnotation() Get the formatted value(s) passed to the customAnnotation method.
 *
 * --------------------------------------------------------------------------
 * General keywords:
 * --------------------------------------------------------------------------
 * @method static type(DataType $type) Set the type keyword of the schema.
 * @method DataType|Collection<int, DataType> getType() Get the value(s) passed to the type method.
 * @method static enum(string|array<int, int|string|bool|\BackedEnum> $enum) Set the enum keyword of the schema.
 * @method string|array<int, int|string|bool|\BackedEnum>|Collection<int, string|array<int, int|string|bool|\BackedEnum>> getEnum() Get the value(s) passed to the enum method.
 * @method static const(mixed $const) Set the const keyword of the schema.
 * @method mixed|Collection<int, mixed> getConst() Get the value(s) passed to the const method.
 * @method static format(string|Format $format) Set the format keyword of the schema.
 * @method string|Format|Collection<int, string|Format> getFormat() Get the value(s) passed to the format method.
 *
 * --------------------------------------------------------------------------
 * Composition keywords:
 * --------------------------------------------------------------------------
 * @method static not(Closure $callback) Set the not keyword of the schema.
 * @method Closure|Collection<int, Closure> getNot() Get the value(s) passed to the not method.
 */
interface Schema extends AppliesKeywords, Pipeable, Whenable
{
    public function __construct();

    public static function make(): static;

    public function tree(SchemaTree $tree): static;

    public function cloneBaseStructure(): static;

    /**
     * Convert the schema to an array.
     *
     * @param  bool  $nested  Whether this schema is nested in another schema.
     * @return array<string, mixed>
     */
    public function toArray(bool $nested = false): array;
}
