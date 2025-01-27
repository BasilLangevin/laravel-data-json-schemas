<?php

use BasilLangevin\LaravelDataSchemas\Attributes\CustomAnnotation;
use BasilLangevin\LaravelDataSchemas\Attributes\Description;
use BasilLangevin\LaravelDataSchemas\Attributes\Title;
use BasilLangevin\LaravelDataSchemas\Schemas\IntegerSchema;
use BasilLangevin\LaravelDataSchemas\Schemas\NullSchema;
use BasilLangevin\LaravelDataSchemas\Schemas\StringSchema;
use BasilLangevin\LaravelDataSchemas\Schemas\UnionSchema;
use BasilLangevin\LaravelDataSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\Filled;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\NotRegex;

covers(UnionSchema::class);

uses(TestsSchemaTransformation::class);

test('applyType creates the set of constituent schemas', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    expect($schema->getConstituentSchemas())->toBeCollection()->toHaveCount(2);
    expect($schema->getConstituentSchemas()->first())->toBeInstanceOf(StringSchema::class);
    expect($schema->getConstituentSchemas()->last())->toBeInstanceOf(IntegerSchema::class);
});

test('applyType adds a constituent null schema if the property is nullable', function () {
    $this->class->addProperty('?string', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    expect($schema->getConstituentSchemas())->toBeCollection()->toHaveCount(2);
    expect($schema->getConstituentSchemas()->first())->toBeInstanceOf(StringSchema::class);
    expect($schema->getConstituentSchemas()->last())->toBeInstanceOf(NullSchema::class);
});

test('applyType never adds more than one constituent null schema', function () {
    $this->class->addProperty('string|int|null', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    expect($schema->getConstituentSchemas())->toBeCollection()->toHaveCount(3);
    expect($schema->getConstituentSchemas()->first())->toBeInstanceOf(StringSchema::class);
    expect($schema->getConstituentSchemas()->get(1))->toBeInstanceOf(IntegerSchema::class);
    expect($schema->getConstituentSchemas()->last())->toBeInstanceOf(NullSchema::class);
});

it('supports titles and descriptions')
    ->expect(fn () => $this->class->addProperty('string | int', 'property', [Title::class => 'Property', Description::class => 'This is a property']))
    ->toHaveSchema('property', [
        'type' => ['string', 'integer'],
        'title' => 'Property',
        'description' => 'This is a property',
    ]);

it('supports default values')
    ->expect(fn () => $this->class->addProperty('string | int', 'property', [], 'test'))
    ->toHaveSchema('property', [
        'type' => ['string', 'integer'],
        'default' => 'test',
    ]);

it('supports custom annotations')
    ->expect(fn () => $this->class->addProperty('string | int', 'property', [CustomAnnotation::class => ['annotation', 'test value']]))
    ->toHaveSchema('property', [
        'type' => ['string', 'integer'],
        'x-annotation' => 'test value',
    ]);

it('accepts keywords for each of its types')
    ->expect(fn () => $this->class->addProperty('string | int', 'property', [Min::class => 42]))
    ->toHaveSchema('property', [
        'type' => ['string', 'integer'],
        'minLength' => 42,
        'minimum' => 42,
    ]);

todo('supports multiple not composition keywords')
    ->expect(fn () => $this->class->addProperty('string | int', 'property', [Filled::class, NotRegex::class => '/test/']))
    ->toHaveSchema('property', [
        'type' => ['string', 'integer'],
        'not' => ['const' => 0],
        'minLength' => 1,
        'pattern' => '/test/',
    ]);

it('can call annotation methods on itself', function () {
    $schema = UnionSchema::make('test');
    $schema->title('test title');

    expect($schema->getTitle())->toBe('test title');
});

it('can call keyword methods on its constituent schemas', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $result = $schema->minLength(42);

    expect($result)->toBeInstanceOf(UnionSchema::class);
    expect($result->getConstituentSchemas()->first()->getMinLength())->toBe(42);
});

it('throws a BadMethodCallException if a keyword method is not supported by any of its constituent schemas', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $schema->minItems(42);
})
    ->throws(\BadMethodCallException::class, 'Method "minItems" not found');

it('applies a keyword to all of its applicable constituent schemas', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $schema->const(null);

    expect($schema->getConstituentSchemas()->first()->getConst())->toBe(null);
    expect($schema->getConstituentSchemas()->last()->getConst())->toBe(null);
});

test('a getter keyword method returns the value from the constituent schema that has the keyword', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $schema->minLength(42);

    expect($schema->getMinLength())->toBe(42);
});

test('a getter keyword method throws an exception if the keyword is not set on any of the constituent schemas', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    expect(fn () => $schema->getMinLength())->toThrow(\Exception::class, 'The keyword "minLength" has not been set.');
});

test('a getter keyword method returns a collection of the values from the constituent schemas when mu.tiple have the keyword', function () {
    $this->class->addProperty('string | int', 'property');
    $property = $this->class->getClassProperty('property');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $schema->const(null);

    expect($schema->getConst())->toBeCollection()->toHaveCount(2)->toArray()->toBe([null, null]);
});

it('can clone its base structure', function () {
    $this->class->addProperty('string|int', 'name');
    $property = $this->class->getClassProperty('name');

    $schema = UnionSchema::make('test');
    $schema->applyType($property);

    $schema->description('test description');

    $clone = $schema->cloneBaseStructure();

    expect($clone)->toBeInstanceOf(UnionSchema::class);
    expect($clone->getName())->toBe('');
    expect(fn () => $clone->getDescription())->toThrow(\Exception::class, 'The keyword "description" has not been set.');

    $constituents = $clone->getConstituentSchemas();
    expect($constituents->first())->toBeInstanceOf(StringSchema::class);
    expect($constituents->last())->toBeInstanceOf(IntegerSchema::class);
});
