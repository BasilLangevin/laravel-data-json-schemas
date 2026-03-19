<?php

use BasilLangevin\LaravelDataJsonSchemas\Actions\MakeSchemaForReflectionType;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\ArraySchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\BooleanSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\IntegerSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\NumberSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\ObjectSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\StringSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\UnionSchema;
use BasilLangevin\LaravelDataJsonSchemas\Support\PropertyWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Tests\Support\Enums\TestIntegerEnum;
use BasilLangevin\LaravelDataJsonSchemas\Tests\Support\Enums\TestStringEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

covers(MakeSchemaForReflectionType::class);

class MakeSchemaForReflectionTypeCollectionInheritance extends Collection {}

class MakeSchemaForReflectionTypeDataCollectionInheritance extends DataCollection {}

enum NonBackedEnum {}

class MakeSchemaForReflectionTypeTest extends Data
{
    public function __construct(
        public array $arrayProperty,
        public bool $boolProperty,
        public float $floatProperty,
        public int $intProperty,
        public object $objectProperty,
        public string $stringProperty,

        public TestStringEnum $stringEnumProperty,
        public TestIntegerEnum $intEnumProperty,
        public NonBackedEnum $nonBackedEnumProperty,

        public DateTimeInterface $dateTimeProperty,
        public CarbonInterface $carbonInterfaceProperty,
        public Carbon $carbonProperty,

        public Collection $collectionProperty,
        public MakeSchemaForReflectionTypeCollectionInheritance $collectionInheritanceProperty,
        public MakeSchemaForReflectionTypeDataCollectionInheritance $dataCollectionProperty,

        public ?string $nullableProperty,
        public string|int $unionProperty,

        public Closure $unsupportedProperty,

        public Collection&Enumerable $unsupportedType,
    ) {}
}

it('creates the correct Schema type from a Data class property', function ($property, $schemaType) {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, $property)->getReflectionType();
    $schema = MakeSchemaForReflectionType::run($wrapper, $property);

    expect($schema)->toBeInstanceOf($schemaType);
})->with([
    ['arrayProperty', ArraySchema::class],
    ['boolProperty', BooleanSchema::class],
    ['floatProperty', NumberSchema::class],
    ['intProperty', IntegerSchema::class],
    ['objectProperty', ObjectSchema::class],
    ['stringProperty', StringSchema::class],
    ['stringEnumProperty', StringSchema::class],
    ['intEnumProperty', IntegerSchema::class],
    ['dateTimeProperty', StringSchema::class],
    ['carbonProperty', StringSchema::class],
    ['collectionProperty', ArraySchema::class],
    ['collectionInheritanceProperty', ArraySchema::class],
    ['dataCollectionProperty', ArraySchema::class],
    ['nullableProperty', UnionSchema::class],
    ['unionProperty', UnionSchema::class],
]);

it('throws an exception if the type is not supported', function () {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, 'unsupportedProperty')->getReflectionType();
    $action = new MakeSchemaForReflectionType;

    $action->handle($wrapper, 'unsupportedProperty');
})->throws(Exception::class, 'JSON Schema transformation is not supported for the "Closure" type.');

it('throws an exception if the enum is not backed', function () {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, 'nonBackedEnumProperty')->getReflectionType();
    $action = new MakeSchemaForReflectionType;

    expect(fn () => $action->handle($wrapper, 'nonBackedEnumProperty'))->toThrow(Exception::class, 'Enum "NonBackedEnum" is not a backed enum.');
});

it('makes a UnionSchema for a nullable type by default', function () {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, 'nullableProperty')->getReflectionType();
    $schema = MakeSchemaForReflectionType::run($wrapper, 'nullableProperty');

    expect($schema)->toBeInstanceOf(UnionSchema::class);
});

it('does not make a UnionSchema for a nullable type if the unionNullableTypes option is false', function () {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, 'nullableProperty')->getReflectionType();
    $action = new MakeSchemaForReflectionType(unionNullableTypes: false);
    $schema = $action->handle($wrapper, 'nullableProperty');

    expect($schema)->not->toBeInstanceOf(UnionSchema::class);
});

it('throws an exception if an intersection type is passed', function () {
    $wrapper = PropertyWrapper::make(MakeSchemaForReflectionTypeTest::class, 'unsupportedType')->getReflectionType();
    $action = new MakeSchemaForReflectionType;

    $action->handle($wrapper, 'unsupportedType');
})->throws(Exception::class, 'JSON Schema transformation is not supported for intersection types.');
