<?php

use BasilLangevin\LaravelDataJsonSchemas\Attributes\CustomAnnotation;
use BasilLangevin\LaravelDataJsonSchemas\Attributes\Title;
use BasilLangevin\LaravelDataJsonSchemas\Support\AttributeWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\ClassWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\DocBlockParser;
use BasilLangevin\LaravelDataJsonSchemas\Support\PropertyWrapper;
use Spatie\LaravelData\Data;

covers(ClassWrapper::class);

#[Title('Test'), CustomAnnotation('test1', 'value1'), CustomAnnotation('test2', 'value2')]
class TestClassWrapperClass extends Data
{
    public string $test;

    public int $testInt;

    public bool $testBoolean;

    protected string $hidden;

    public function test()
    {
        return 'test';
    }

    /**
     * A constructor with parameters.
     */
    public function __construct(
        string $test,
        int $testInt,
        bool $testBoolean,
    ) {}
}

class TestInvalidClassWrapperClass {}

it('throws an exception if the class is not a data class', function () {
    ClassWrapper::make(TestInvalidClassWrapperClass::class);
})->throws(InvalidArgumentException::class, 'Only data classes are supported.');

it('can get its name')
    ->expect(fn () => ClassWrapper::make(TestClassWrapperClass::class)->getName())
    ->toBe('TestClassWrapperClass');

it('can get its short name')
    ->expect(fn () => ClassWrapper::make(TestClassWrapperClass::class)->getShortName())
    ->toBe('TestClassWrapperClass');

it('can check if it has an attribute', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->hasAttribute(Title::class))->toBe(true);
});

it('can get an attribute', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->getAttribute(Title::class))
        ->toBeInstanceOf(AttributeWrapper::class)
        ->getValue()->toBe('Test');
});

it('can get multiple attributes of the same type', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->getAttribute(CustomAnnotation::class))
        ->toBeCollection()
        ->toHaveCount(2)
        ->each->toBeInstanceOf(AttributeWrapper::class);
});

it('can get its properties', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->properties())
        ->toBeCollection()
        ->toHaveCount(3)
        ->each->toBeInstanceOf(PropertyWrapper::class);

    expect($reflector->properties()->first()->getName())
        ->toBe('test');
});

it('can get a property', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->getProperty('test'))
        ->toBeInstanceOf(PropertyWrapper::class)
        ->getName()->toBe('test');
});

it('throws an exception if a property is not found', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    $reflector->getProperty('nonExistentProperty');
})->throws(Exception::class, 'Property "nonExistentProperty" not found in class "TestClassWrapperClass"');

it('can check if it has a constructor', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->hasConstructor())->toBe(true);
});

it('can get the constructor', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->getConstructor())->toBeInstanceOf(ReflectionMethod::class);
});

it('can get the constructor doc block', function () {
    $reflector = ClassWrapper::make(TestClassWrapperClass::class);

    expect($reflector->getConstructorDocBlock())->toBeInstanceOf(DocBlockParser::class);
});

test('getConstructorDocBlock returns null if the class does not have a constructor', function () {
    class NoConstructorTestClass extends Data {}

    $reflector = ClassWrapper::make(NoConstructorTestClass::class);

    expect($reflector->getConstructorDocBlock())->toBeNull();
});

test('getConstructorDocBlock returns null if the constructor has no doc block', function () {
    class NoDocBlockConstructorTestClass extends Data
    {
        public function __construct() {}
    }

    $reflector = ClassWrapper::make(NoDocBlockConstructorTestClass::class);

    expect($reflector->getConstructorDocBlock())->toBeNull();
});
