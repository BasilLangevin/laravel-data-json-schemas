<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns;

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Annotation\DescriptionKeyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Contracts\Schema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\StringSchema;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;

covers(SingleTypeSchemaTrait::class);

uses(TestsSchemaTransformation::class);

class SingleTypeSchemaTestSchema implements Schema
{
    use SingleTypeSchemaTrait;

    public static array $keywords = [
        DescriptionKeyword::class,
    ];
}

it('can convert to an array')
    ->expect(SingleTypeSchemaTestSchema::make())
    ->description('test description')
    ->toArray()
    ->toBe([
        'description' => 'test description',
    ]);

it('can apply its type', function () {
    $schema = StringSchema::make();
    $schema->applyType();

    expect($schema->getType())->toBe(DataType::String);
});

test('applyType throws an exception if the type is not set', function () {
    $schema = SingleTypeSchemaTestSchema::make();
    $schema->applyType();
})->throws(\Exception::class, 'SingleType schemas must have a $type.');

it('can clone its base structure', function () {
    $schema = SingleTypeSchemaTestSchema::make();
    $schema->description('test description');

    $clone = $schema->cloneBaseStructure();

    expect($clone)->toBeInstanceOf(SingleTypeSchemaTestSchema::class);
    expect(fn () => $clone->getDescription())->toThrow(\Exception::class, 'The keyword "description" has not been set.');
});
