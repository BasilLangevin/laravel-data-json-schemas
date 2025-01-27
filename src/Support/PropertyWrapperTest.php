<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;
use BasilLangevin\LaravelDataSchemas\Attributes\Title;
use BasilLangevin\LaravelDataSchemas\Support\ClassWrapper;
use BasilLangevin\LaravelDataSchemas\Support\PropertyWrapper;
use BasilLangevin\LaravelDataSchemas\Support\AttributeWrapper;
use BasilLangevin\LaravelDataSchemas\Attributes\CustomAnnotation;
use BasilLangevin\LaravelDataSchemas\Tests\Support\Enums\TestStringEnum;

covers(PropertyWrapper::class);

class TestPropertyWrapperClass
{
    #[Title('Test'), CustomAnnotation('test1', 'value1'), CustomAnnotation('test2', 'value2')]
    public string $test;

    public array $testArray;

    public bool $testBoolean;

    public TestStringEnum $testEnum;

    public float $testFloat;

    public int $testInt;

    public object $testObject;

    public DateTime $testDateTime;

    public DateTimeInterface $testDateTimeInterface;

    public CarbonInterface $testCarbonInterface;

    public Carbon $testCarbon;

    public string|int $testUnion;

    protected string $hidden;

    public function test()
    {
        return 'test';
    }
}

it('can get the constituent types of a union property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testUnion');

    expect($property->getTypes())->toBeCollection()->toHaveCount(2);
    expect($property->getTypes()->first()->getName())->toBe('string');
    expect($property->getTypes()->last()->getName())->toBe('int');
});

it('can get the types of a single type property', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->getTypes())->toBeCollection()->toHaveCount(1);
    expect($property->getTypes()->first()->getName())->toBe('string');
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
    ['isArray'],
    ['isBoolean'],
    ['isEnum'],
    ['isFloat'],
    ['isInteger'],
    ['isNumber'],
    ['isObject'],
    ['isString'],
]);

it('can check if the reflected property is an array', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testArray');

    expect($reflector->isArray())->toBe(true);
});

test('isArray returns false if the reflected property is not an array', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isArray())->toBe(false);
});

it('can check if the reflected property is a boolean', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testBoolean');

    expect($reflector->isBoolean())->toBe(true);
});

test('isBoolean returns false if the reflected property is not a boolean', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isBoolean())->toBe(false);
});

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

it('can check if the reflected property is an enum', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testEnum');

    expect($reflector->isEnum())->toBe(true);
});

test('isEnum returns false if the reflected property is not an enum', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isEnum())->toBe(false);
});

it('can check if the reflected property is a float', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testFloat');

    expect($reflector->isFloat())->toBe(true);
});

test('isFloat returns false if the reflected property is not a float', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isFloat())->toBe(false);
});

it('can check if the reflected property is an integer', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testInt');

    expect($reflector->isInteger())->toBe(true);
});

test('isInteger returns false if the reflected property is not an integer', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isInteger())->toBe(false);
});

it('can check if a reflected float property is a number', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testFloat');

    expect($reflector->isNumber())->toBe(true);
});

it('can check if a reflected int property is a number', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testInt');

    expect($reflector->isNumber())->toBe(true);
});

test('isNumber returns false if the reflected property is not a number', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isNumber())->toBe(false);
});

it('can check if the reflected property is an object', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testObject');

    expect($property->isObject())->toBe(true);
});

test('isObject returns false if the reflected property is not an object', function () {
    $property = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($property->isObject())->toBe(false);
});

it('can check if the reflected property is a string', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->isString())->toBe(true);
});

test('isString returns false if the reflected property is not a string', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'testInt');

    expect($reflector->isString())->toBe(false);
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
        ->toHaveCount(11)
        ->each->toBeInstanceOf(PropertyWrapper::class);

    expect($reflector->siblings()->map->getName()->toArray())
        ->toBe(['testArray', 'testBoolean', 'testEnum', 'testFloat', 'testInt', 'testObject', 'testDateTime', 'testDateTimeInterface', 'testCarbonInterface', 'testCarbon', 'testUnion']);
});

it('can get its sibling names', function () {
    $reflector = PropertyWrapper::make(TestPropertyWrapperClass::class, 'test');

    expect($reflector->siblingNames())->toBeCollection();
    expect($reflector->siblingNames()->toArray())->toBe(['testArray', 'testBoolean', 'testEnum', 'testFloat', 'testInt', 'testObject', 'testDateTime', 'testDateTimeInterface', 'testCarbonInterface', 'testCarbon', 'testUnion']);
});
