<?php

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\General\TypeKeyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\BooleanSchema;

covers(TypeKeyword::class);

it('can set its type value')
    ->expect((new BooleanSchema)->type(DataType::Boolean->value))
    ->getType()->toBe(DataType::Boolean->value);

it('can set its type to a DataType enum value')
    ->expect((new BooleanSchema)->type(DataType::Boolean))
    ->getType()->toBe(DataType::Boolean);

it('can apply the type value to a schema')
    ->expect((new BooleanSchema)->type(DataType::Boolean->value))
    ->applyKeyword(TypeKeyword::class, collect())
    ->toEqual(collect([
        'type' => DataType::Boolean->value,
    ]));

it('can apply a DataType enum value to a schema')
    ->expect((new BooleanSchema)->type(DataType::Boolean))
    ->applyKeyword(TypeKeyword::class, collect())
    ->toEqual(collect([
        'type' => DataType::Boolean->value,
    ]));
