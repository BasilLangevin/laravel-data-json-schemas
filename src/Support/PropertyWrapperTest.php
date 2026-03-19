<?php

use BasilLangevin\LaravelDataJsonSchemas\Attributes\CustomAnnotation;
use BasilLangevin\LaravelDataJsonSchemas\Attributes\Title;
use BasilLangevin\LaravelDataJsonSchemas\Support\AttributeWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\ClassWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\PropertyWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Tests\Integration\DataClasses\PersonData;
use BasilLangevin\LaravelDataJsonSchemas\Tests\Support\Enums\TestStringEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataPropertyType;
use Spatie\LaravelData\Support\Types\Type;

covers(PropertyWrapper::class);

class TestPropertyWrapperClass extends Data
{
    #[Title('Test'), CustomAnnotation('test1', 'value1'), CustomAnnotation('test2', 'value2')]
    public string $test;

    public array $testArray;

    public Collection $testCollection;

    public Collection&Enumerable $testIntersection;

    public bool $testBoolean;

    public TestStringEnum $testEnum;

    public float $testFloat;

    public int $testInt;

    public object $testObject;

    public DateTime $testDateTime;

    public DateTimeInterface $testDateTimeInterface;

    public CarbonInterface $testCarbonInterface;

    public Carbon $testCarbon;

    public ?string $testNullable;

    public string|int $testUnion;

    public string|int|null $testNullableUnion;

    public PersonData $testDataObject;

    protected string $hidden;

    protected $typeless;

    public function test()
    {
        return 'test';
    }
}

it('can get its reflection instance', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getReflection())->toBeInstanceOf(ReflectionProperty::class);
    expect($property->getReflection()->getName())->toBe('test');
});

it('can get its data property instance', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getDataProperty())->toBeInstanceOf(DataProperty::class);
    expect($property->getDataProperty()->name)->toBe('test');
});

it('can get its data type', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getDataType())->toBeInstanceOf(DataPropertyType::class);
    expect($property->getDataType()->type)->toBeInstanceOf(Type::class)->name->toBe('string');
});

it('can get its reflection type', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    $type = $property->getReflectionType();

    expect($type)->toBeInstanceOf(ReflectionNamedType::class);

    /** @var ReflectionNamedType $type */
    expect($type->getName())->toBe('string');
});

it('can get the reflection types of a property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getReflectionTypes())->toBeCollection()->toHaveCount(1);
    expect($property->getReflectionTypes()->first())
        ->toBeInstanceOf(ReflectionNamedType::class)
        ->getName()->toBe('string');
});

it('can get the reflection types of an intersection property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testIntersection');

    expect($property->getReflectionTypes())->toBeCollection()->toHaveCount(2);
    expect($property->getReflectionTypes()->first())
        ->toBeInstanceOf(ReflectionNamedType::class)
        ->getName()->toBe(Collection::class);
    expect($property->getReflectionTypes()->last())
        ->toBeInstanceOf(ReflectionNamedType::class)
        ->getName()->toBe(Enumerable::class);
});

it('can get the reflection types of a union property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($property->getReflectionTypes())->toBeCollection()->toHaveCount(2);
    expect($property->getReflectionTypes()->first())
        ->toBeInstanceOf(ReflectionNamedType::class)
        ->getName()->toBe('string');
    expect($property->getReflectionTypes()->last())
        ->toBeInstanceOf(ReflectionNamedType::class)
        ->getName()->toBe('int');
});

it('can get the constituent types of a union property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($property->getTypes())->toBeCollection()->toHaveCount(2);
    expect($property->getTypes()->first()->name)->toBe('string');
    expect($property->getTypes()->last()->name)->toBe('int');
});

it('can get the types of a single type property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getTypes())->toBeCollection()->toHaveCount(1);
    expect($property->getTypes()->first()->name)->toBe('string');
});

it('can get the type names of a union property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($property->getTypeNames())->toBeCollection()->toHaveCount(2);
    expect($property->getTypeNames()->first())->toBe('string');
    expect($property->getTypeNames()->last())->toBe('int');
});

it('can get the type names of a single type property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getTypeNames())->toBeCollection()->toHaveCount(1);
    expect($property->getTypeNames()->first())->toBe('string');
});

it('can check if the reflected property has a given type', function (string $property, string $type) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->hasType($type))->toBe(true);
})->with([
    ['test', '*'],
    ['test', 'string'],
    ['testArray', 'array'],
    ['testBoolean', 'boolean'],
    ['testFloat', 'number'],
    ['testInt', 'integer'],
    ['testObject', 'object'],
]);

it('can check if the reflected property has a given type name', function (string $property, string $type) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->hasTypeName($type))->toBe(true);
})->with([
    ['test', 'string'],
    ['testArray', 'array'],
    ['testBoolean', 'bool'],
    ['testFloat', 'float'],
    ['testInt', 'int'],
    ['testObject', 'object'],
]);

test('hasTypeName returns false if the reflected property does not have a given type name', function (string $property, string $type) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->hasTypeName($type))->toBe(false);
})->with([
    ['test', 'integer'],
    ['testArray', 'string'],
    ['testBoolean', 'float'],
    ['testFloat', 'bool'],
    ['testInt', 'string'],
    ['testEnum', 'nonexistent'],
]);

test('hasTypeName returns false if the reflected property is a union', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($reflector->hasTypeName('string'))->toBe(false);
});

test('hasType returns false if the reflected property does not have a given type', function (string $property, string $type) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->hasType($type))->toBe(false);
})->with([
    ['test', 'integer'],
    ['testArray', 'string'],
    ['testBoolean', 'number'],
    ['testFloat', 'boolean'],
    ['testInt', 'string'],
    ['testEnum', 'nonexistent'],
]);

test('property type checks return false if the property is a union', function (string $method) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($reflector->$method())->toBe(false);
})->with([
    ['isEnum'],
    ['isDataObject'],
    ['isArray'],
]);

it('can check if the reflected property is a DateTime', function ($property) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->isDateTime())->toBe(true);
})->with([
    ['testDateTime'],
    ['testDateTimeInterface'],
    ['testCarbonInterface'],
    ['testCarbon'],
]);

test('isDateTime returns false if the reflected property is not a DateTime', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isDateTime())->toBe(false);
});

test('isDateTime returns false if the reflected property is a union', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($reflector->isDateTime())->toBe(false);
});

it('can check if the reflected property is an array', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testArray');

    expect($reflector->isArray())->toBe(true);
});

it('can check if a reflected Collection property is an array', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testCollection');

    expect($reflector->isArray())->toBe(true);
});

test('isArray returns false if the reflected property is not an array', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isArray())->toBe(false);
});

it('can check if the reflected property is an enum', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testEnum');

    expect($reflector->isEnum())->toBe(true);
});

test('isEnum returns false if the reflected property is not an enum', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isEnum())->toBe(false);
});

test('isEnum returns false if the reflected property is a union', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($reflector->isEnum())->toBe(false);
});

it('can check if the reflected property is nullable', function (string $property) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->isNullable())->toBe(true);
})->with([
    ['testNullable'],
    ['testNullableUnion'],
]);

test('isNullable returns false if the reflected property is not nullable', function (string $property) {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, $property);

    expect($reflector->isNullable())->toBe(false);
})->with([
    ['test'],
    ['testUnion'],
]);

it('can check if the reflected property is a data object', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testDataObject');

    expect($property->isDataObject())->toBe(true);
});

test('isDataObject returns false if the reflected property is not a data object', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->isDataObject())->toBe(false);
});

it('can get the data class name of the property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testDataObject');

    expect($property->getDataClassName())->toBe(PersonData::class);
});

it('cannot get the data class name of a union type', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($property->getDataClassName())->toBeNull();
});

it('can get the data class of the property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testDataObject');

    expect($property->getDataClass())->toBeInstanceOf(ClassWrapper::class);
    expect($property->getDataClass()->getName())->toBe(PersonData::class);
});

test('getDataClass returns null if the reflected property is not a data object', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getDataClass())->toBeNull();
});

it('can check if it has an attribute', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->hasAttribute(Title::class))->toBe(true);
});

it('can get an attribute', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->getAttribute(Title::class))
        ->toBeInstanceOf(AttributeWrapper::class)
        ->getName()->toBe(Title::class)
        ->getValue()->toBe('Test');
});

it('can get multiple attributes of the same type', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->attributes(CustomAnnotation::class))
        ->toBeCollection()
        ->toHaveCount(2)
        ->each->toBeInstanceOf(AttributeWrapper::class);

    expect($reflector->attributes(CustomAnnotation::class)->map->getName()->toArray())
        ->toBe([CustomAnnotation::class, CustomAnnotation::class]);
});

it('can get its declaring class', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->getClass())->toBeInstanceOf(ClassWrapper::class);
    expect($reflector->getClass()->getName())->toBe(TestPropertyWrapperClass::class);
});

it('can get its siblings', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->siblings())->toBeCollection()
        ->toHaveCount(16)
        ->each->toBeInstanceOf(PropertyWrapper::class);

    expect($reflector->siblings()->map->getName()->toArray())
        ->toBe(['testArray', 'testCollection', 'testIntersection', 'testBoolean', 'testEnum', 'testFloat', 'testInt', 'testObject', 'testDateTime', 'testDateTimeInterface', 'testCarbonInterface', 'testCarbon', 'testNullable', 'testUnion', 'testNullableUnion', 'testDataObject']);
});

it('can get its sibling names', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->siblingNames())->toBeCollection();
    expect($reflector->siblingNames()->toArray())->toBe(['testArray', 'testCollection', 'testIntersection', 'testBoolean', 'testEnum', 'testFloat', 'testInt', 'testObject', 'testDateTime', 'testDateTimeInterface', 'testCarbonInterface', 'testCarbon', 'testNullable', 'testUnion', 'testNullableUnion', 'testDataObject']);
});
