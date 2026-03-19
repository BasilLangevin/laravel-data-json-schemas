<?php

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Exceptions\SchemaConfigurationException;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\General\EnumKeyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\IntegerSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\StringSchema;

covers(EnumKeyword::class);

enum KeywordTestEnum
{
    case First;
    case Second;
    case Third;
}

enum KeywordTestStringEnum: string
{
    case First = 'first';
    case Second = 'second';
    case Third = 'third';
}

enum KeywordTestIntegerEnum: int
{
    case First = 1;
    case Second = 2;
    case Third = 3;
}

$basicOutput = collect([
    'type' => DataType::String->value,
]);

$basicIntegerOutput = collect([
    'type' => DataType::Integer->value,
]);

it('can set its enum to a backed enum')
    ->expect(StringSchema::make()->enum(KeywordTestStringEnum::class))
    ->getEnum()->toBe(KeywordTestStringEnum::class);

it('can set its enum to an integer enum')
    ->expect(IntegerSchema::make()->enum(KeywordTestIntegerEnum::class))
    ->getEnum()->toBe(KeywordTestIntegerEnum::class);

it('cannot set its enum to a non-backed enum', function () use ($basicOutput) {
    StringSchema::make()->enum(KeywordTestEnum::class)->applyKeyword(EnumKeyword::class, $basicOutput);
})
    ->throws(InvalidArgumentException::class, "Enum 'KeywordTestEnum' is not a backed enum. Only backed enums are supported.");

it('cannot set its enum to a non-enum', function () use ($basicOutput) {
    StringSchema::make()->enum('KeywordTest')->applyKeyword(EnumKeyword::class, $basicOutput);
})
    ->throws(InvalidArgumentException::class, "Enum 'KeywordTest' is not a valid enum.");

it('can set its enum to an array of values')
    ->expect(StringSchema::make()->enum([KeywordTestStringEnum::First, KeywordTestStringEnum::Second]))
    ->getEnum()->toBe([KeywordTestStringEnum::First, KeywordTestStringEnum::Second]);

it('can get its enum')
    ->expect(StringSchema::make()->enum(KeywordTestStringEnum::class))
    ->getEnum()->toBe(KeywordTestStringEnum::class);

it('can apply a backed enum to a schema')
    ->expect(StringSchema::make()->enum(KeywordTestStringEnum::class))
    ->applyKeyword(EnumKeyword::class, $basicOutput)
    ->toEqual(collect([
        'type' => DataType::String->value,
        'enum' => [KeywordTestStringEnum::First->value, KeywordTestStringEnum::Second->value, KeywordTestStringEnum::Third->value],
    ]));

it('adds value descriptions when applying an integer enum to a schema')
    ->expect(IntegerSchema::make()->enum(KeywordTestIntegerEnum::class))
    ->applyKeyword(EnumKeyword::class, $basicIntegerOutput)
    ->toEqual(collect([
        'type' => DataType::Integer->value,
        'enum' => [KeywordTestIntegerEnum::First->value, KeywordTestIntegerEnum::Second->value, KeywordTestIntegerEnum::Third->value],
        'x-enum-values' => ['First' => KeywordTestIntegerEnum::First->value, 'Second' => KeywordTestIntegerEnum::Second->value, 'Third' => KeywordTestIntegerEnum::Third->value],
    ]));

it('can apply an array of values to a schema')
    ->expect(StringSchema::make()->enum([KeywordTestStringEnum::Second, 'Third', 4]))
    ->applyKeyword(EnumKeyword::class, $basicOutput)
    ->toEqual(collect([
        'type' => DataType::String->value,
        'enum' => [KeywordTestStringEnum::Second->value, 'Third', 4],
    ]));

it('cannot apply non-backed enum values to a schema', function () use ($basicOutput) {
    StringSchema::make()
        ->enum([KeywordTestEnum::First, KeywordTestEnum::Second])
        ->applyKeyword(EnumKeyword::class, $basicOutput);
})
    ->throws(SchemaConfigurationException::class, 'Non-backed enum values are not supported.');

it('applies only the common values when multiple enums are applied')
    ->expect(StringSchema::make()->enum([KeywordTestStringEnum::First, KeywordTestStringEnum::Second])->enum([KeywordTestStringEnum::Second, KeywordTestStringEnum::Third]))
    ->applyKeyword(EnumKeyword::class, $basicOutput)
    ->toEqual(collect([
        'type' => DataType::String->value,
        'enum' => [KeywordTestStringEnum::Second->value],
    ]));

it('throws an exception if multiple enums are applied with no overlapping values')
    ->expect(StringSchema::make()->enum([KeywordTestStringEnum::First, KeywordTestStringEnum::Second])->enum([KeywordTestStringEnum::Third]))
    ->applyKeyword(EnumKeyword::class, $basicOutput)
    ->throws(SchemaConfigurationException::class, 'Multiple enums were set with no overlapping values.');
